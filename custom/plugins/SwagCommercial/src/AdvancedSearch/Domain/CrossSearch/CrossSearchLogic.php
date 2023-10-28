<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\CrossSearch;

use OpenSearch\Client;
use OpenSearchDSL\Query\Compound\BoolQuery;
use OpenSearchDSL\Search;
use Shopware\Commercial\AdvancedSearch\Domain\Configuration\ConfigurationLoader;
use Shopware\Commercial\AdvancedSearch\Domain\CrossSearch\Converter\AbstractCrossSearchConverter;
use Shopware\Commercial\AdvancedSearch\Domain\Search\TokenQueryBuilder;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\Filter\AbstractTokenFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\Tokenizer;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Elasticsearch\Framework\ElasticsearchHelper;
use Shopware\Elasticsearch\Product\SearchFieldConfig;

#[Package('buyers-experience')]
class CrossSearchLogic extends AbstractCrossSearchLogic
{
    /**
     * @param iterable<AbstractCrossSearchConverter> $converters
     * @param array<string, bool> $crossSearch
     *
     * @internal
     */
    public function __construct(
        private readonly AbstractTokenFilter $tokenFilter,
        private readonly Tokenizer $tokenizer,
        private readonly ConfigurationLoader $configurationLoader,
        private readonly TokenQueryBuilder $tokenQueryBuilder,
        private readonly Client $client,
        private readonly ElasticsearchHelper $helper,
        private readonly iterable $converters,
        private readonly array $crossSearch,
        private readonly string $timeout = '5s'
    ) {
    }

    public function build(EntityDefinition $searchDefinition, Criteria $criteria, Context $context): BoolQuery
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return new BoolQuery();
        }

        if (!$context->getSource() instanceof SalesChannelApiSource) {
            return new BoolQuery();
        }

        $salesChannelId = $context->getSource()->getSalesChannelId();
        $searchConfig = $this->configurationLoader->load($salesChannelId);

        $isAndSearch = $searchConfig['andLogic'] === true;

        $tokens = $this->tokenizer->tokenize((string) $criteria->getTerm());
        $tokens = $this->tokenFilter->filter($tokens, $context);
        $crossSearch = [];

        foreach ($searchConfig['searchableFields'][$searchDefinition->getEntityName()] as $item) {
            $fields = EntityDefinitionQueryHelper::getFieldsOfAccessor($searchDefinition, (string) $item['field']);

            $first = array_shift($fields);

            if (!$first instanceof AssociationField) {
                continue;
            }

            $second = array_shift($fields);

            if (!$second instanceof Field) {
                continue;
            }

            $definition = $first->getReferenceDefinition();

            if ($first instanceof ManyToManyAssociationField) {
                $definition = $first->getToManyReferenceDefinition();
            }

            if (!$this->crossSearchEnabled($searchDefinition->getEntityName(), $definition->getEntityName())) {
                continue;
            }

            $crossSearch[$definition->getEntityName()] ??= new CrossSearchFieldCollection($definition);
            $crossSearch[$definition->getEntityName()]->add(new SearchFieldConfig($second->getPropertyName(), (int) $item['ranking'], (bool) $item['tokenize']));
        }

        $crossIndexRequest = [];

        foreach ($crossSearch as $crossEntity => $configs) {
            $crossQuery = new BoolQuery();

            foreach ($tokens as $token) {
                $tokenBool = new BoolQuery();

                foreach ($configs->getSearchConfigs() as $config) {
                    $tokenBool->add($this->tokenQueryBuilder->build($configs->getCrossDefinition(), $token, $config, $context), BoolQuery::SHOULD);
                }

                $crossQuery->add($tokenBool, $isAndSearch ? BoolQuery::MUST : BoolQuery::SHOULD);
            }

            $crossSearch = $this->createCrossSearch($crossQuery, $searchDefinition->getEntityName(), $crossEntity);

            if ($crossSearch === null) {
                continue;
            }

            $crossSearchArray = $crossSearch->toArray();
            $crossSearchArray['timeout'] = $this->timeout;

            $crossIndexRequest[] = ['index' => $this->helper->getIndexName($configs->getCrossDefinition())];
            $crossIndexRequest[] = $crossSearchArray;
        }

        if (empty($crossIndexRequest)) {
            return new BoolQuery();
        }

        /** @var array{responses: array{aggregations: array<string, array{buckets: array{key: array<string>}}>}} $responses */
        $responses = $this->client->msearch(['body' => $crossIndexRequest]);

        $result = $this->parseResponse($responses);

        $bool = new BoolQuery();

        foreach ($result as $aggName => $aggResult) {
            foreach ($this->converters as $converter) {
                if ($converter->getName() !== $aggName) {
                    continue;
                }

                $bool->add($converter->convertToQuery($aggResult), BoolQuery::SHOULD);
            }
        }

        return $bool;
    }

    public function getDecorated(): AbstractCrossSearchLogic
    {
        throw new DecorationPatternException(self::class);
    }

    private function createCrossSearch(BoolQuery $crossQuery, string $searchEntity, string $crossIndexEntity): ?Search
    {
        $aggName = sprintf('%s.%s.aggregation', $searchEntity, $crossIndexEntity);

        foreach ($this->converters as $converter) {
            if ($converter->getName() !== $aggName) {
                continue;
            }

            $crossSearch = new Search();
            $crossSearch->setSize(0);
            $crossSearch->addQuery($crossQuery);
            $aggregation = $converter->getAggregation();
            $aggregation->addParameter('size', ElasticsearchHelper::MAX_SIZE_VALUE);
            $crossSearch->addAggregation($aggregation);

            return $crossSearch;
        }

        return null;
    }

    private function crossSearchEnabled(string $searchEntity, string $crossEntity): bool
    {
        $crossSearchName = $searchEntity . '.' . $crossEntity;

        if (empty($this->crossSearch) || !\array_key_exists($crossSearchName, $this->crossSearch)) {
            return false;
        }

        return $this->crossSearch[$crossSearchName] === true;
    }

    /**
     * @param array{responses: array{aggregations: array<string, array{buckets: array{key: string[]}}>}} $responses
     *
     * @return array<string, string[]>
     */
    private function parseResponse(array $responses): array
    {
        $result = [];

        if (empty($responses['responses'])) {
            return [];
        }

        foreach ($responses['responses'] as $response) {
            if (empty($response['aggregations'])) {
                continue;
            }

            $aggregation = $response['aggregations'];

            foreach ($aggregation as $agg => $aggBucket) {
                if (empty($aggBucket['buckets'])) {
                    continue;
                }

                $ids = array_column($aggBucket['buckets'], 'key');
                $result[$agg] = $result[$agg] ?? [];
                $result[$agg] = array_merge($result[$agg], $ids);
            }
        }

        return $result;
    }
}

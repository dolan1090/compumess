<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Search;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Commercial\AdvancedSearch\Domain\Configuration\ConfigurationLoader;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\Filter\AbstractTokenFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\Tokenizer;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Elasticsearch\Product\SearchFieldConfig;

#[Package('buyers-experience')]
class SearchLogic extends AbstractSearchLogic
{
    /**
     * @param array<string, bool> $crossSearch
     *
     * @internal
     */
    public function __construct(
        private readonly AbstractTokenFilter $tokenFilter,
        private readonly Tokenizer $tokenizer,
        private readonly ConfigurationLoader $configurationLoader,
        private readonly TokenQueryBuilder $tokenQueryBuilder,
        private readonly array $crossSearch
    ) {
    }

    public function build(EntityDefinition $definition, Criteria $criteria, Context $context): BoolQuery
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
        $fields = [];

        foreach ($searchConfig['searchableFields'][$definition->getEntityName()] as $item) {
            if ($this->crossSearchEnabled($definition, (string) $item['field'])) {
                continue;
            }

            $config = new SearchFieldConfig((string) $item['field'], (int) $item['ranking'], (bool) $item['tokenize']);

            $fields[] = $config;
        }

        $bool = new BoolQuery();

        foreach ($tokens as $token) {
            $tokenBool = new BoolQuery();

            foreach ($fields as $config) {
                $tokenBool->add($this->tokenQueryBuilder->build($definition, $token, $config, $context), BoolQuery::SHOULD);
            }

            $bool->add($tokenBool, $isAndSearch ? BoolQuery::MUST : BoolQuery::SHOULD);
        }

        return $bool;
    }

    public function getDecorated(): AbstractSearchLogic
    {
        throw new DecorationPatternException(self::class);
    }

    private function crossSearchEnabled(EntityDefinition $definition, string $field): bool
    {
        $fields = EntityDefinitionQueryHelper::getFieldsOfAccessor($definition, $field);

        $first = array_shift($fields);

        if (!$first instanceof AssociationField) {
            return false;
        }

        $crossDefinition = $first->getReferenceDefinition();

        if ($first instanceof ManyToManyAssociationField) {
            $crossDefinition = $first->getToManyReferenceDefinition();
        }

        $crossSearchName = $definition->getEntityName() . '.' . $crossDefinition->getEntityName();

        if (empty($this->crossSearch) || !\array_key_exists($crossSearchName, $this->crossSearch)) {
            return false;
        }

        return $this->crossSearch[$crossSearchName] === true;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Suggest;

use Shopware\Commercial\AdvancedSearch\Domain\Boosting\BoostingQueryBuilder;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\BoostingQueryStruct;
use Shopware\Commercial\AdvancedSearch\Domain\Completion\CompletionSearcher;
use Shopware\Commercial\AdvancedSearch\Domain\Configuration\ConfigurationLoader;
use Shopware\Commercial\AdvancedSearch\Domain\Search\SearchTermExtractor;
use Shopware\Commercial\AdvancedSearch\Event\MultiContentSuggestCriteriaEvent;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\Suggest\AbstractProductSuggestRoute;
use Shopware\Core\Content\Product\SalesChannel\Suggest\ProductSuggestRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('buyers-experience')]
class ProductSuggestRouteDecorator extends AbstractProductSuggestRoute
{
    /**
     * @param iterable<AbstractElasticsearchDefinition> $esDefinitions
     *
     * @internal
     */
    public function __construct(
        private readonly AbstractProductSuggestRoute $decorated,
        private readonly iterable $esDefinitions,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly SearchTermExtractor $searchTermExtractor,
        private readonly ConfigurationLoader $configurationLoader,
        private readonly CompletionSearcher $completionSearcher,
        private readonly BoostingQueryBuilder $boostingQueryBuilder
    ) {
    }

    public function getDecorated(): AbstractProductSuggestRoute
    {
        return $this->decorated;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSuggestRouteResponse
    {
        if (!License::get('ADVANCED_SEARCH-1376205') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $this->decorated->load($request, $context, $criteria);
        }

        $definition = $this->definitionInstanceRegistry->getByEntityName(ProductDefinition::ENTITY_NAME);

        $criteria = $this->processCriteria($criteria, $request, $definition, $context);

        $this->dispatcher->dispatch(new MultiContentSuggestCriteriaEvent($definition, $criteria, $context));

        $productResponse = $this->decorated->load($request, $context, $criteria);

        $completion = $criteria->getTerm() ? $this->completionSearcher->search($criteria->getTerm()) : [];

        $result = new MultiSuggestResult();

        foreach ($this->esDefinitions as $esDefinition) {
            $definition = $esDefinition->getEntityDefinition();

            if ($definition->getEntityName() === ProductDefinition::ENTITY_NAME) {
                continue;
            }

            $repository = $this->definitionInstanceRegistry->getRepository($definition->getEntityName());

            $criteria = $this->processCriteria(new Criteria(), $request, $definition, $context);

            $this->dispatcher->dispatch(new MultiContentSuggestCriteriaEvent($definition, $criteria, $context));

            $entityResult = $repository->search($criteria, $context->getContext());

            $result->addSuggest($entityResult, $repository->getDefinition()->getEntityName());
        }

        $listingResult = $productResponse->getListingResult();
        $listingResult->addExtension('multiSuggestResult', $result);
        $listingResult->addExtension('completionResult', new ArrayStruct($completion));

        return $productResponse;
    }

    private function processCriteria(Criteria $criteria, Request $request, EntityDefinition $definition, SalesChannelContext $context): Criteria
    {
        if ($boosting = $this->boostingQueryBuilder->build($definition, $context->getContext())) {
            $criteria->addExtension(BoostingQueryStruct::CRITERIA_EXTENSION, new BoostingQueryStruct($boosting));
        }

        $criteria->resetQueries();

        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE)
            ->setTerm($this->searchTermExtractor->fromRequest($request))
            ->addState(Criteria::STATE_ELASTICSEARCH_AWARE);

        $config = $this->configurationLoader->load($context->getSalesChannelId());

        if (empty($config['hitCount'][$definition->getEntityName()]['maxSuggestCount'])) {
            return $criteria;
        }

        $limit = $config['esEnabled'] ? (int) $config['hitCount'][$definition->getEntityName()]['maxSuggestCount'] : null;

        if (!$limit) {
            return $criteria;
        }

        $request->request->set('limit', $limit);
        $request->query->set('limit', $limit);
        $criteria->setLimit($limit);

        return $criteria;
    }
}

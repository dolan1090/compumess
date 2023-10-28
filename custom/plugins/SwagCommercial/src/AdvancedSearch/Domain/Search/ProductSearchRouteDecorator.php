<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Search;

use Shopware\Commercial\AdvancedSearch\Domain\Boosting\BoostingQueryBuilder;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\BoostingQueryStruct;
use Shopware\Commercial\AdvancedSearch\Domain\Configuration\ConfigurationLoader;
use Shopware\Commercial\AdvancedSearch\Domain\PreviewSearch\PreviewSearchService;
use Shopware\Commercial\AdvancedSearch\Event\MultiContentSearchCriteriaEvent;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\Search\AbstractProductSearchRoute;
use Shopware\Core\Content\Product\SalesChannel\Search\ProductSearchRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('buyers-experience')]
class ProductSearchRouteDecorator extends AbstractProductSearchRoute
{
    /**
     * @param iterable<AbstractElasticsearchDefinition> $esDefinitions
     *
     * @internal
     */
    public function __construct(
        private readonly AbstractProductSearchRoute $decorated,
        private readonly iterable $esDefinitions,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly SearchTermExtractor $searchTermExtractor,
        private readonly ConfigurationLoader $configurationLoader,
        private readonly BoostingQueryBuilder $boostingQueryBuilder
    ) {
    }

    public function getDecorated(): AbstractProductSearchRoute
    {
        return $this->decorated;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): ProductSearchRouteResponse
    {
        if (!License::get('ADVANCED_SEARCH-1376205') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $this->decorated->load($request, $context, $criteria);
        }

        $definition = $this->definitionInstanceRegistry->getByEntityName(ProductDefinition::ENTITY_NAME);
        $criteria = $this->processCriteria($criteria, $request, $definition, $context);

        $this->dispatcher->dispatch(new MultiContentSearchCriteriaEvent($definition, $criteria, $context));

        $productResponse = $this->decorated->load($request, $context, $criteria);

        $result = new MultiSearchResult();

        foreach ($this->esDefinitions as $esDefinition) {
            $definition = $esDefinition->getEntityDefinition();

            if ($definition->getEntityName() === ProductDefinition::ENTITY_NAME) {
                continue;
            }

            $repository = $this->definitionInstanceRegistry->getRepository($definition->getEntityName());

            $multiContentCriteria = new Criteria();
            $multiContentCriteria->addState(...$criteria->getStates());
            $multiContentCriteria = $this->processCriteria($multiContentCriteria, $request, $definition, $context);

            $this->dispatcher->dispatch(new MultiContentSearchCriteriaEvent($definition, $multiContentCriteria, $context));

            $entityResult = $repository->search($multiContentCriteria, $context->getContext());

            $result->addSearch($entityResult, $repository->getDefinition()->getEntityName());
        }

        $listingResult = $productResponse->getListingResult();
        $listingResult->addExtension('multiSearchResult', $result);

        return $productResponse;
    }

    private function processCriteria(Criteria $criteria, Request $request, EntityDefinition $definition, SalesChannelContext $context): Criteria
    {
        if ($boosting = $this->boostingQueryBuilder->build($definition, $context->getContext())) {
            $criteria->addExtension(BoostingQueryStruct::CRITERIA_EXTENSION, new BoostingQueryStruct($boosting));
        }

        $criteria->resetQueries();

        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT)
            ->setTerm($this->searchTermExtractor->fromRequest($request))
            ->addState(Criteria::STATE_ELASTICSEARCH_AWARE);

        // In Preview mode, pagination is handled by $request instead of config
        if ($criteria->hasState(PreviewSearchService::PREVIEW_MODE_STATE)) {
            $this->handlePagination($request, $criteria);

            return $criteria;
        }

        $config = $this->configurationLoader->load($context->getSalesChannelId());

        if (empty($config['hitCount'][$definition->getEntityName()]['maxSearchCount'])) {
            return $criteria;
        }

        $limit = $config['esEnabled'] ? (int) $config['hitCount'][$definition->getEntityName()]['maxSearchCount'] : null;

        if (!$limit) {
            return $criteria;
        }

        $request->request->set('limit', $limit);
        $request->query->set('limit', $limit);
        $criteria->setLimit($limit);

        return $criteria;
    }

    private function handlePagination(Request $request, Criteria $criteria): void
    {
        $limit = $this->getLimit($request);
        $page = $this->getPage($request);

        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setLimit($limit);
    }

    private function getLimit(Request $request): int
    {
        $limit = $request->query->getInt('limit', 24);

        if ($request->isMethod(Request::METHOD_POST)) {
            $limit = $request->request->getInt('limit', $limit);
        }

        return $limit <= 0 ? 24 : $limit;
    }

    private function getPage(Request $request): int
    {
        $page = $request->query->getInt('p', 1);

        if ($request->isMethod(Request::METHOD_POST)) {
            $page = $request->request->getInt('p', $page);
        }

        return $page <= 0 ? 1 : $page;
    }
}

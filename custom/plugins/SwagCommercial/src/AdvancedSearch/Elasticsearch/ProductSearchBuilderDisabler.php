<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Elasticsearch;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Elasticsearch\Framework\ElasticsearchHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @description This class help disable the \Shopware\Elasticsearch\Product\ProductSearchBuilder as AdvancedSearch will handle with own service
 */
#[Package('buyers-experience')]
class ProductSearchBuilderDisabler implements ProductSearchBuilderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $decorated,
        private readonly ElasticsearchHelper $helper,
        private readonly ProductDefinition $productDefinition
    ) {
    }

    public function build(Request $request, Criteria $criteria, SalesChannelContext $context): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            $this->decorated->build($request, $criteria, $context);

            return;
        }

        if (!$this->helper->allowSearch($this->productDefinition, $context->getContext(), $criteria)) {
            $this->decorated->build($request, $criteria, $context);
        }

        // do nothing, search criteria will be handled in MultiContent search routes
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Order;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Domain\Product\MultiWarehouseProductFilter;
use Shopware\Core\Content\Product\DataAbstractionLayer\StockUpdate\AbstractStockUpdateFilter;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;

/**
 * Stock updater filter to exclude product ids which are assigned to a warehouse
 *
 * @internal
 */
#[Package('inventory')]
class ExcludeMultiWarehouseStockUpdateFilter extends AbstractStockUpdateFilter
{
    public function __construct(private readonly MultiWarehouseProductFilter $productFilter)
    {
    }

    /**
     * @param string[] $ids
     *
     * @return array<string>
     */
    public function filter(array $ids, Context $context): array
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            return $ids;
        }

        return $this->productFilter->filterProductIdsWithoutWarehouses($ids, $context);
    }
}

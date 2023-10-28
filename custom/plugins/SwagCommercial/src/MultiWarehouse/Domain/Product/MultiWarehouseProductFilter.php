<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Product;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class MultiWarehouseProductFilter
{
    public function __construct(private readonly EntityRepository $productRepository)
    {
    }

    /**
     * @param string[] $ids
     *
     * @return string[]
     */
    public function filterProductIdsWithWarehouses(array $ids, Context $context): array
    {
        return $this->fetchProductIdsWithWarehouseAssigned($ids, $context);
    }

    /**
     * @param string[] $ids
     *
     * @return string[]
     */
    public function filterProductIdsWithoutWarehouses(array $ids, Context $context): array
    {
        return \array_values(
            \array_diff($ids, $this->filterProductIdsWithWarehouses($ids, $context))
        );
    }

    /**
     * @param string[] $ids
     *
     * @return string[]
     */
    private function fetchProductIdsWithWarehouseAssigned(array $ids, Context $context): array
    {
        if (!$ids) {
            return [];
        }

        $criteria = new Criteria($ids);
        $criteria->addAssociation('warehouseGroups');
        $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
            new EqualsFilter('warehouseGroups.id', null),
        ]));

        /** @var string[] $ids */
        $ids = $this->productRepository
            ->searchIds($criteria, $context)
            ->getIds();

        return $ids;
    }
}

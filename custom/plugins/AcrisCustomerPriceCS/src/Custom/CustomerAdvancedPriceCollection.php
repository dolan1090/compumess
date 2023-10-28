<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                    add(CustomerAdvancedPriceEntity $entity)
 * @method void                    set(string $key, CustomerAdvancedPriceEntity $entity)
 * @method CustomerAdvancedPriceEntity[]    getIterator()
 * @method CustomerAdvancedPriceEntity[]    getElements()
 * @method CustomerAdvancedPriceEntity|null get(string $key)
 * @method CustomerAdvancedPriceEntity|null first()
 * @method CustomerAdvancedPriceEntity|null last()
 */
class CustomerAdvancedPriceCollection extends EntityCollection
{
    public function sortByQuantity(): void
    {
        $this->sort(function (CustomerAdvancedPriceEntity $a, CustomerAdvancedPriceEntity $b) {
            return $a->getQuantityStart() <=> $b->getQuantityStart();
        });
    }

    protected function getExpectedClass(): string
    {
        return CustomerAdvancedPriceEntity::class;
    }
}

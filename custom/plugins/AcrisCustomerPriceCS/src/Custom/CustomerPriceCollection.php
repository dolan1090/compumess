<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(CustomerPriceEntity $entity)
 * @method void              set(string $key, CustomerPriceEntity $entity)
 * @method CustomerPriceEntity[]    getIterator()
 * @method CustomerPriceEntity[]    getElements()
 * @method CustomerPriceEntity|null get(string $key)
 * @method CustomerPriceEntity|null first()
 * @method CustomerPriceEntity|null last()
 */
class CustomerPriceCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CustomerPriceEntity::class;
    }
}

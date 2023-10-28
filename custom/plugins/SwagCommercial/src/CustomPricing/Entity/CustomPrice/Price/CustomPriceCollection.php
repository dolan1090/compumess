<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Entity\CustomPrice\Price;

use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @method CustomPrice[]    getIterator()
 * @method CustomPrice[]    getElements()
 * @method CustomPrice|null get(string $currencyId)
 * @method CustomPrice|null first()
 * @method CustomPrice|null last()
 */
#[Package('inventory')]
class CustomPriceCollection extends PriceCollection
{
    public function getApiAlias(): string
    {
        return 'custom_price_collection';
    }

    protected function getExpectedClass(): ?string
    {
        return CustomPrice::class;
    }
}

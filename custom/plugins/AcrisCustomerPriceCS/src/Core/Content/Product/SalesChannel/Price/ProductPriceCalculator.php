<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Core\Content\Product\SalesChannel\Price;

use Acris\CustomerPrice\Components\CustomerPrice\CustomerPriceService;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Content\Product\SalesChannel\Price\ProductPriceCalculator as ParentClass;

class ProductPriceCalculator extends ParentClass
{
    public function __construct(private readonly AbstractProductPriceCalculator $parent, private readonly CustomerPriceService $customerPriceService)
    {
    }

    public function getDecorated(): AbstractProductPriceCalculator
    {
        return $this->parent->getDecorated();
    }

    public function calculate(iterable $products, SalesChannelContext $context): void
    {
        $this->parent->calculate($products, $context);
        $this->customerPriceService->calculateProductPrices($products, $context);
    }
}

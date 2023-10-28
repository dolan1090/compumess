<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Core\Checkout\Cart\Price\Struct;

use Shopware\Core\Checkout\Cart\Price\Struct\ListPrice;
use Shopware\Core\Framework\Util\FloatComparator;

class AcrisListPrice extends ListPrice
{
    /**
     * @var float
     */
    protected $price;

    /**
     * @var float
     */
    protected $discount;

    /**
     * @var float
     */
    protected $percentage;


    public function __construct(float $price, float $discount, float $percentage)
    {
        $this->price = FloatComparator::cast($price);
        $this->discount = FloatComparator::cast($discount);
        $this->percentage = FloatComparator::cast($percentage);
    }

}

<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Components\Struct;

use Shopware\Core\Framework\Struct\Struct;

class LineItemCustomerPriceStruct extends Struct
{
    private float $originalUnitPrice;

    public function __construct( float $originalUnitPrice )
    {
        $this->originalUnitPrice = $originalUnitPrice;
    }

    /**
     * @return float
     */
    public function getOriginalUnitPrice(): float
    {
        return $this->originalUnitPrice;
    }

    /**
     * @param float $originalUnitPrice
     */
    public function setOriginalUnitPrice(float $originalUnitPrice): void
    {
        $this->originalUnitPrice = $originalUnitPrice;
    }
}

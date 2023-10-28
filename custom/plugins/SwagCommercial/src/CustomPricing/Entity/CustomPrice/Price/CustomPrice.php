<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Entity\CustomPrice\Price;

use Shopware\Core\Framework\DataAbstractionLayer\Pricing\Price;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class CustomPrice extends Price
{
    /**
     * @param array{net: float, gross:float} $percentage
     */
    public function __construct(
        string $currencyId,
        float $net,
        float $gross,
        bool $linked,
        private int $quantityStart = 1,
        private ?int $quantityEnd = null,
        ?Price $listPrice = null,
        ?array $percentage = null,
        ?Price $regulationPrice = null
    ) {
        parent::__construct($currencyId, $net, $gross, $linked, $listPrice, $percentage, $regulationPrice);
    }

    public function getQuantityStart(): int
    {
        return $this->quantityStart;
    }

    public function setQuantityStart(int $quantityStart): void
    {
        $this->quantityStart = $quantityStart;
    }

    public function getQuantityEnd(): ?int
    {
        return $this->quantityEnd;
    }

    public function setQuantityEnd(?int $quantityEnd): void
    {
        $this->quantityEnd = $quantityEnd;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Entity\Field;

use Shopware\Commercial\CustomPricing\Entity\FieldSerializer\CustomPriceFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class CustomPriceField extends JsonField
{
    public function __construct(
        string $storageName,
        string $propertyName
    ) {
        parent::__construct($storageName, $propertyName);
    }

    protected function getSerializerClass(): string
    {
        return CustomPriceFieldSerializer::class;
    }
}

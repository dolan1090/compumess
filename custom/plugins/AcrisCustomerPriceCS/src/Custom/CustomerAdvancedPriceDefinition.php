<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Custom;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CustomerAdvancedPriceDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'acris_customer_advanced_price';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CustomerAdvancedPriceCollection::class;
    }

    public function getEntityClass(): string
    {
        return CustomerAdvancedPriceEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new VersionField(),
            (new FkField('customer_price_id', 'customerPriceId', CustomerPriceDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(CustomerPriceDefinition::class))->addFlags(new Required()),
            (new PriceField('price', 'price'))->addFlags(new Required()),
            (new IntField('quantity_start', 'quantityStart'))->addFlags(new Required()),
            new IntField('quantity_end', 'quantityEnd'),
            (new ManyToOneAssociationField('customerPrice', 'customer_price_id', CustomerPriceDefinition::class, 'id', false)),
            new CustomFields(),
        ]);
    }
}

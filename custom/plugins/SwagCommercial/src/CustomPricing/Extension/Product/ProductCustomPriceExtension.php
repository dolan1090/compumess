<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Extension\Product;

use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductCustomPriceExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('customPrice', CustomPriceDefinition::class, 'product_id'))
                ->addFlags(new CascadeDelete(), new Inherited())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinitionClass(): string
    {
        return ProductDefinition::class;
    }
}

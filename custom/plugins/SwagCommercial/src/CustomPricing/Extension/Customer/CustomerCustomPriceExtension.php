<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Extension\Customer;

use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class CustomerCustomPriceExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('customPrice', CustomPriceDefinition::class, 'customer_id'))
                ->addFlags(new CascadeDelete())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDefinitionClass(): string
    {
        return CustomerDefinition::class;
    }
}

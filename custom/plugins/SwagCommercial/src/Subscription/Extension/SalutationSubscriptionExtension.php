<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress\SubscriptionAddressDefinition;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionCustomer\SubscriptionCustomerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Salutation\SalutationDefinition;

#[Package('checkout')]
class SalutationSubscriptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return SalutationDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('subscriptionCustomers', SubscriptionCustomerDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
        );

        $collection->add(
            (new OneToManyAssociationField('subscriptionCustomerAddresses', SubscriptionAddressDefinition::class, 'salutation_id', 'id'))->addFlags(new SetNullOnDelete()),
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress\SubscriptionAddressDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Country\CountryDefinition;

#[Package('checkout')]
class CountrySubscriptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return CountryDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('subscriptionCustomerAddresses', SubscriptionAddressDefinition::class, 'country_id', 'id'))->addFlags(new RestrictDelete()),
        );
    }
}

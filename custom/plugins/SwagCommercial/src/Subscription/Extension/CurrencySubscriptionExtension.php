<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Currency\CurrencyDefinition;

#[Package('checkout')]
class CurrencySubscriptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return CurrencyDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'subscriptions',
                SubscriptionDefinition::class,
                'currency_id',
            ))->addFlags(new RestrictDelete()),
        );
    }
}

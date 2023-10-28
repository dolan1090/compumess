<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Extension;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class PaymentMethodSubscriptionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return PaymentMethodDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add((new OneToManyAssociationField('subscriptions', SubscriptionDefinition::class, 'payment_method_id', 'id'))->addFlags(new RestrictDelete()));
    }
}

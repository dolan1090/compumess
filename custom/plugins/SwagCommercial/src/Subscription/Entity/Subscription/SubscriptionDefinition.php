<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress\SubscriptionAddressDefinition;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionCustomer\SubscriptionCustomerDefinition;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionTag\SubscriptionTagMappingDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Payment\PaymentMethodDefinition;
use Shopware\Core\Checkout\Shipping\ShippingMethodDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AutoIncrementField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CashRoundingConfigField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CronIntervalField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateIntervalField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\NoConstraint;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Currency\CurrencyDefinition;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\NumberRange\DataAbstractionLayer\NumberRangeField;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\Tag\TagDefinition;

#[Package('checkout')]
class SubscriptionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'subscription';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SubscriptionEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SubscriptionCollection::class;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return [
            'initialExecutionCount' => 0,
            'remainingExecutionCount' => 0,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),

            (new JsonField('converted_order', 'convertedOrder'))->addFlags(new Required(), new ApiAware()),
            (new NumberRangeField('subscription_number', 'subscriptionNumber'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING, false), new Required()),
            new AutoIncrementField(),

            (new DateTimeField('next_schedule', 'nextSchedule'))->addFlags(new Required(), new ApiAware()),

            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new FkField('subscription_plan_id', 'subscriptionPlanId', SubscriptionPlanDefinition::class))->addFlags(new ApiAware()),
            (new StringField('subscription_plan_name', 'subscriptionPlanName'))->addFlags(new Required(), new ApiAware()),

            (new FkField('subscription_interval_id', 'subscriptionIntervalId', SubscriptionIntervalDefinition::class))->addFlags(new ApiAware()),
            (new StringField('subscription_interval_name', 'subscriptionIntervalName'))->addFlags(new Required(), new ApiAware()),
            (new DateIntervalField('date_interval', 'dateInterval'))->addFlags(new Required(), new ApiAware()),
            (new CronIntervalField('cron_interval', 'cronInterval'))->addFlags(new Required(), new ApiAware()),

            (new IntField('initial_execution_count', 'initialExecutionCount'))->addFlags(new ApiAware(), new Required()),
            (new IntField('remaining_execution_count', 'remainingExecutionCount'))->addFlags(new ApiAware(), new Required()),

            (new FkField('billing_address_id', 'billingAddressId', SubscriptionAddressDefinition::class))->addFlags(new Required(), new ApiAware(), new CascadeDelete(), new NoConstraint()),
            (new FkField('shipping_address_id', 'shippingAddressId', SubscriptionAddressDefinition::class))->addFlags(new Required(), new ApiAware(), new CascadeDelete(), new NoConstraint()),
            (new FkField('shipping_method_id', 'shippingMethodId', ShippingMethodDefinition::class))->addFlags(new Required(), new ApiAware()),
            (new FkField('payment_method_id', 'paymentMethodId', PaymentMethodDefinition::class))->addFlags(new Required(), new ApiAware()),

            (new FkField('currency_id', 'currencyId', CurrencyDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new ApiAware(), new Required()),

            (new StateMachineStateField('state_id', 'stateId', SubscriptionStates::STATE_MACHINE))->addFlags(new Required()),

            (new CustomFields())->addFlags(new ApiAware()),

            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false),
            (new ManyToOneAssociationField('subscriptionPlan', 'subscription_plan_id', SubscriptionPlanDefinition::class, 'id', false))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING, false)),
            (new ManyToOneAssociationField('subscriptionInterval', 'subscription_interval_id', SubscriptionIntervalDefinition::class, 'id', false))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING, false)),
            (new OneToOneAssociationField('subscriptionCustomer', 'id', 'subscription_id', SubscriptionCustomerDefinition::class, false))->addFlags(new ApiAware(), new CascadeDelete(), new SearchRanking(0.5)),
            (new OneToOneAssociationField('billingAddress', 'billing_address_id', 'id', SubscriptionAddressDefinition::class, false))->addFlags(new ApiAware()),
            (new OneToOneAssociationField('shippingAddress', 'shipping_address_id', 'id', SubscriptionAddressDefinition::class, false))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('addresses', SubscriptionAddressDefinition::class, 'subscription_id'))->addFlags(new ApiAware(), new CascadeDelete(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new ManyToOneAssociationField('paymentMethod', 'payment_method_id', PaymentMethodDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('shippingMethod', 'shipping_method_id', ShippingMethodDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('stateMachineState', 'state_id', 'state_machine_state', 'id', false))->addFlags(new ApiAware()),
            (new OneToManyAssociationField('orders', OrderDefinition::class, 'subscription_id'))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField('tags', TagDefinition::class, SubscriptionTagMappingDefinition::class, 'subscription_id', 'tag_id'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::ASSOCIATION_SEARCH_RANKING)),
            (new ManyToOneAssociationField('currency', 'currency_id', CurrencyDefinition::class, 'id', false))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class, 'id', false))->addFlags(new ApiAware()),

            (new CashRoundingConfigField('item_rounding', 'itemRounding'))->addFlags(new Required()),
            (new CashRoundingConfigField('total_rounding', 'totalRounding'))->addFlags(new Required()),
        ]);
    }
}

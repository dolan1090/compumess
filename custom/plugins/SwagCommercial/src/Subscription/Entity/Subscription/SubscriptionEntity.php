<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress\SubscriptionAddressCollection;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress\SubscriptionAddressEntity;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionCustomer\SubscriptionCustomerEntity;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;
use Shopware\Core\System\Tag\TagCollection;

#[Package('checkout')]
class SubscriptionEntity extends Entity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $convertedOrder = [];

    protected string $subscriptionNumber;

    protected int $autoIncrement;

    protected \DateTimeInterface $nextSchedule;

    protected string $salesChannelId;

    protected string $subscriptionPlanId;

    protected string $subscriptionPlanName;

    protected string $subscriptionIntervalId;

    protected string $subscriptionIntervalName;

    protected DateInterval $dateInterval;

    protected CronInterval $cronInterval;

    protected int $initialExecutionCount = 0;

    protected int $remainingExecutionCount = 0;

    protected string $billingAddressId;

    protected string $shippingAddressId;

    protected string $paymentMethodId;

    protected string $currencyId;

    protected string $languageId;

    protected string $shippingMethodId;

    protected string $stateId;

    protected ?SalesChannelEntity $salesChannel = null;

    protected ?StateMachineStateEntity $stateMachineState = null;

    protected ?SubscriptionPlanEntity $subscriptionPlan = null;

    protected ?SubscriptionIntervalEntity $subscriptionInterval = null;

    protected ?SubscriptionCustomerEntity $subscriptionCustomer = null;

    protected ?PaymentMethodEntity $paymentMethod = null;

    protected ?SubscriptionAddressEntity $billingAddress = null;

    protected ?SubscriptionAddressEntity $shippingAddress = null;

    protected ?ShippingMethodEntity $shippingMethod = null;

    protected ?LanguageEntity $language = null;

    protected ?CurrencyEntity $currency = null;

    protected ?SubscriptionAddressCollection $addresses = null;

    protected CashRoundingConfig $itemRounding;

    protected CashRoundingConfig $totalRounding;

    protected ?OrderCollection $orders = null;

    protected ?TagCollection $tags = null;

    /**
     * @return array<string, mixed>
     */
    public function getConvertedOrder(): array
    {
        return $this->convertedOrder;
    }

    /**
     * @param array<string, mixed> $convertedOrder
     */
    public function setConvertedOrder(array $convertedOrder): void
    {
        $this->convertedOrder = $convertedOrder;
    }

    public function getSubscriptionNumber(): string
    {
        return $this->subscriptionNumber;
    }

    public function setSubscriptionNumber(string $subscriptionNumber): void
    {
        $this->subscriptionNumber = $subscriptionNumber;
    }

    public function getAutoIncrement(): int
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(int $autoIncrement): void
    {
        $this->autoIncrement = $autoIncrement;
    }

    public function getNextSchedule(): \DateTimeInterface
    {
        return $this->nextSchedule;
    }

    public function setNextSchedule(\DateTimeInterface $nextSchedule): void
    {
        $this->nextSchedule = $nextSchedule;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getSubscriptionPlanId(): string
    {
        return $this->subscriptionPlanId;
    }

    public function setSubscriptionPlanId(string $subscriptionPlanId): void
    {
        $this->subscriptionPlanId = $subscriptionPlanId;
    }

    public function getSubscriptionPlanName(): string
    {
        return $this->subscriptionPlanName;
    }

    public function setSubscriptionPlanName(string $subscriptionPlanName): void
    {
        $this->subscriptionPlanName = $subscriptionPlanName;
    }

    public function getSubscriptionIntervalId(): string
    {
        return $this->subscriptionIntervalId;
    }

    public function setSubscriptionIntervalId(string $subscriptionIntervalId): void
    {
        $this->subscriptionIntervalId = $subscriptionIntervalId;
    }

    public function getSubscriptionIntervalName(): string
    {
        return $this->subscriptionIntervalName;
    }

    public function setSubscriptionIntervalName(string $subscriptionIntervalName): void
    {
        $this->subscriptionIntervalName = $subscriptionIntervalName;
    }

    public function getDateInterval(): DateInterval
    {
        return $this->dateInterval;
    }

    public function setDateInterval(DateInterval $dateInterval): void
    {
        $this->dateInterval = $dateInterval;
    }

    public function getCronInterval(): CronInterval
    {
        return $this->cronInterval;
    }

    public function setCronInterval(CronInterval $cronInterval): void
    {
        $this->cronInterval = $cronInterval;
    }

    public function getInitialExecutionCount(): int
    {
        return $this->initialExecutionCount;
    }

    public function setInitialExecutionCount(int $initialExecutionCount): void
    {
        $this->initialExecutionCount = $initialExecutionCount;
    }

    public function getRemainingExecutionCount(): int
    {
        return $this->remainingExecutionCount;
    }

    public function setRemainingExecutionCount(int $remainingExecutionCount): void
    {
        $this->remainingExecutionCount = $remainingExecutionCount;
    }

    public function getBillingAddressId(): string
    {
        return $this->billingAddressId;
    }

    public function setBillingAddressId(string $billingAddressId): void
    {
        $this->billingAddressId = $billingAddressId;
    }

    public function getShippingAddressId(): string
    {
        return $this->shippingAddressId;
    }

    public function setShippingAddressId(string $shippingAddressId): void
    {
        $this->shippingAddressId = $shippingAddressId;
    }

    public function getPaymentMethodId(): string
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(string $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getCurrencyId(): string
    {
        return $this->currencyId;
    }

    public function setCurrencyId(string $currencyId): void
    {
        $this->currencyId = $currencyId;
    }

    public function getLanguageId(): string
    {
        return $this->languageId;
    }

    public function setLanguageId(string $languageId): void
    {
        $this->languageId = $languageId;
    }

    public function getShippingMethodId(): string
    {
        return $this->shippingMethodId;
    }

    public function setShippingMethodId(string $shippingMethodId): void
    {
        $this->shippingMethodId = $shippingMethodId;
    }

    public function getStateId(): string
    {
        return $this->stateId;
    }

    public function setStateId(string $stateId): void
    {
        $this->stateId = $stateId;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(?SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getStateMachineState(): ?StateMachineStateEntity
    {
        return $this->stateMachineState;
    }

    public function setStateMachineState(?StateMachineStateEntity $stateMachineState): void
    {
        $this->stateMachineState = $stateMachineState;
    }

    public function getSubscriptionPlan(): ?SubscriptionPlanEntity
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(?SubscriptionPlanEntity $subscriptionPlan): void
    {
        $this->subscriptionPlan = $subscriptionPlan;
    }

    public function getSubscriptionInterval(): ?SubscriptionIntervalEntity
    {
        return $this->subscriptionInterval;
    }

    public function setSubscriptionInterval(?SubscriptionIntervalEntity $subscriptionInterval): void
    {
        $this->subscriptionInterval = $subscriptionInterval;
    }

    public function getSubscriptionCustomer(): ?SubscriptionCustomerEntity
    {
        return $this->subscriptionCustomer;
    }

    public function setSubscriptionCustomer(?SubscriptionCustomerEntity $subscriptionCustomer): void
    {
        $this->subscriptionCustomer = $subscriptionCustomer;
    }

    public function getPaymentMethod(): ?PaymentMethodEntity
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethodEntity $paymentMethod): void
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getBillingAddress(): ?SubscriptionAddressEntity
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?SubscriptionAddressEntity $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    public function getShippingAddress(): ?SubscriptionAddressEntity
    {
        return $this->shippingAddress;
    }

    public function setShippingAddress(?SubscriptionAddressEntity $shippingAddress): void
    {
        $this->shippingAddress = $shippingAddress;
    }

    public function getShippingMethod(): ?ShippingMethodEntity
    {
        return $this->shippingMethod;
    }

    public function setShippingMethod(?ShippingMethodEntity $shippingMethod): void
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getAddresses(): ?SubscriptionAddressCollection
    {
        return $this->addresses;
    }

    public function setAddresses(SubscriptionAddressCollection $addresses): void
    {
        $this->addresses = $addresses;
    }

    public function getOrders(): ?OrderCollection
    {
        return $this->orders;
    }

    public function setOrders(OrderCollection $orders): void
    {
        $this->orders = $orders;
    }

    public function getTags(): ?TagCollection
    {
        return $this->tags;
    }

    public function setTags(TagCollection $tags): void
    {
        $this->tags = $tags;
    }

    public function getLanguage(): ?LanguageEntity
    {
        return $this->language;
    }

    public function setLanguage(?LanguageEntity $language): void
    {
        $this->language = $language;
    }

    public function getCurrency(): ?CurrencyEntity
    {
        return $this->currency;
    }

    public function setCurrency(?CurrencyEntity $currency): void
    {
        $this->currency = $currency;
    }

    public function getItemRounding(): CashRoundingConfig
    {
        return $this->itemRounding;
    }

    public function setItemRounding(CashRoundingConfig $itemRounding): void
    {
        $this->itemRounding = $itemRounding;
    }

    public function getTotalRounding(): CashRoundingConfig
    {
        return $this->totalRounding;
    }

    public function setTotalRounding(CashRoundingConfig $totalRounding): void
    {
        $this->totalRounding = $totalRounding;
    }

    /**
     * @return array<mixed>
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();

        $data['cronInterval'] = (string) $this->cronInterval;
        $data['dateInterval'] = (string) $this->dateInterval;

        return $data;
    }
}

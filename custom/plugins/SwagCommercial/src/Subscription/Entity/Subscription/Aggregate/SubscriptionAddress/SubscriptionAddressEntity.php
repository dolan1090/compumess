<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionAddress;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionAddressEntity extends CustomerAddressEntity
{
    use EntityCustomFieldsTrait;
    use EntityIdTrait;

    protected string $subscriptionId;

    protected string $vatId;

    protected ?SubscriptionEntity $subscription = null;

    protected ?SubscriptionEntity $billingSubscription = null;

    protected ?SubscriptionEntity $shippingSubscription = null;

    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId(string $subscriptionId): void
    {
        $this->subscriptionId = $subscriptionId;
    }

    public function getVatId(): string
    {
        return $this->vatId;
    }

    public function setVatId(string $vatId): void
    {
        $this->vatId = $vatId;
    }

    public function getSubscription(): ?SubscriptionEntity
    {
        return $this->subscription;
    }

    public function setSubscription(SubscriptionEntity $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function getBillingSubscription(): ?SubscriptionEntity
    {
        return $this->billingSubscription;
    }

    public function setBillingSubscription(?SubscriptionEntity $billingSubscription): void
    {
        $this->billingSubscription = $billingSubscription;
    }

    public function getShippingSubscription(): ?SubscriptionEntity
    {
        return $this->shippingSubscription;
    }

    public function setShippingSubscription(?SubscriptionEntity $shippingSubscription): void
    {
        $this->shippingSubscription = $shippingSubscription;
    }
}

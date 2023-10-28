<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionIntervalTranslationEntity extends TranslationEntity
{
    protected string $subscriptionIntervalId;

    protected ?string $name;

    protected SubscriptionIntervalEntity $subscriptionInterval;

    public function getSubscriptionIntervalId(): string
    {
        return $this->subscriptionIntervalId;
    }

    public function setSubscriptionIntervalId(string $subscriptionIntervalId): void
    {
        $this->subscriptionIntervalId = $subscriptionIntervalId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getSubscriptionInterval(): SubscriptionIntervalEntity
    {
        return $this->subscriptionInterval;
    }

    public function setSubscriptionInterval(SubscriptionIntervalEntity $subscriptionIntervalEntity): void
    {
        $this->subscriptionInterval = $subscriptionIntervalEntity;
    }
}

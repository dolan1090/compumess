<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionInterval;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation\SubscriptionIntervalTranslationCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanCollection;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionIntervalEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected bool $active;

    protected ?SubscriptionIntervalTranslationCollection $translations = null;

    protected DateInterval $dateInterval;

    protected CronInterval $cronInterval;

    protected ?string $availabilityRuleId = null;

    protected ?RuleEntity $availabilityRule = null;

    protected ?SubscriptionPlanCollection $subscriptionPlans = null;

    protected ?SubscriptionCollection $subscriptions = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getTranslations(): ?SubscriptionIntervalTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(SubscriptionIntervalTranslationCollection $translations): void
    {
        $this->translations = $translations;
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

    public function getAvailabilityRuleId(): ?string
    {
        return $this->availabilityRuleId;
    }

    public function setAvailabilityRuleId(?string $availabilityRuleId): void
    {
        $this->availabilityRuleId = $availabilityRuleId;
    }

    public function getAvailabilityRule(): ?RuleEntity
    {
        return $this->availabilityRule;
    }

    public function setAvailabilityRule(RuleEntity $availabilityRule): void
    {
        $this->availabilityRule = $availabilityRule;
    }

    public function getSubscriptionPlans(): ?SubscriptionPlanCollection
    {
        return $this->subscriptionPlans;
    }

    public function setSubscriptionPlans(SubscriptionPlanCollection $subscriptionPlans): void
    {
        $this->subscriptionPlans = $subscriptionPlans;
    }

    public function getSubscriptions(): ?SubscriptionCollection
    {
        return $this->subscriptions;
    }

    public function setSubscriptions(SubscriptionCollection $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
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

<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\Aggregate\SubscriptionIntervalTranslation\SubscriptionIntervalTranslationCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionPlanEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected ?string $description = null;

    protected float $discountPercentage;

    protected ?string $label = null;

    protected bool $active;

    protected ?int $minimumExecutionCount = null;

    protected bool $activeStorefrontLabel = false;

    protected ?string $availabilityRuleId = null;

    protected ?RuleEntity $availabilityRule = null;

    protected ?SubscriptionIntervalCollection $subscriptionIntervals = null;

    protected ?ProductCollection $products = null;

    protected ?SubscriptionCollection $subscriptions = null;

    protected ?SubscriptionIntervalTranslationCollection $translations = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDiscountPercentage(): float
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(float $discountPercentage): void
    {
        $this->discountPercentage = $discountPercentage;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getMinimumExecutionCount(): ?int
    {
        return $this->minimumExecutionCount;
    }

    public function setMinimumExecutionCount(?int $minimumExecutionCount): void
    {
        $this->minimumExecutionCount = $minimumExecutionCount;
    }

    public function isActiveStorefrontLabel(): bool
    {
        return $this->activeStorefrontLabel;
    }

    public function setActiveStorefrontLabel(bool $activeStorefrontLabel): void
    {
        $this->activeStorefrontLabel = $activeStorefrontLabel;
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

    public function getSubscriptionIntervals(): ?SubscriptionIntervalCollection
    {
        return $this->subscriptionIntervals;
    }

    public function setSubscriptionIntervals(SubscriptionIntervalCollection $subscriptionIntervals): void
    {
        $this->subscriptionIntervals = $subscriptionIntervals;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }

    public function getSubscriptions(): ?SubscriptionCollection
    {
        return $this->subscriptions;
    }

    public function setSubscriptions(SubscriptionCollection $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
    }

    public function getTranslations(): ?SubscriptionIntervalTranslationCollection
    {
        return $this->translations;
    }

    public function setTranslations(SubscriptionIntervalTranslationCollection $translations): void
    {
        $this->translations = $translations;
    }
}

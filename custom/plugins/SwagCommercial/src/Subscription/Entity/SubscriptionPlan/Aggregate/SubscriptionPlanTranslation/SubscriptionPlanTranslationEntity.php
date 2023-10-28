<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\SubscriptionPlan\Aggregate\SubscriptionPlanTranslation;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionPlanTranslationEntity extends TranslationEntity
{
    protected string $subscriptionPlanId;

    protected ?string $name;

    protected ?string $description;

    protected ?string $label = null;

    protected SubscriptionPlanEntity $subscriptionPlan;

    public function getSubscriptionPlanId(): string
    {
        return $this->subscriptionPlanId;
    }

    public function setSubscriptionPlanId(string $subscriptionPlanId): void
    {
        $this->subscriptionPlanId = $subscriptionPlanId;
    }

    public function getName(): ?string
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

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getSubscriptionPlan(): SubscriptionPlanEntity
    {
        return $this->subscriptionPlan;
    }

    public function setSubscriptionPlan(SubscriptionPlanEntity $subscriptionPlanEntity): void
    {
        $this->subscriptionPlan = $subscriptionPlanEntity;
    }
}

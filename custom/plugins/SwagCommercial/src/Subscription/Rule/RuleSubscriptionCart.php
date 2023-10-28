<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Rule;

use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

#[Package('checkout')]
class RuleSubscriptionCart extends Rule
{
    final public const RULE_NAME = 'subscriptionCart';

    /**
     * @internal
     */
    public function __construct(
        protected bool $isSubscriptionCart = false
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        $isCurrentlySubscriptionCart = $scope
            ->getSalesChannelContext()
            ->hasExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);

        if ($this->isSubscriptionCart) {
            return $isCurrentlySubscriptionCart;
        }

        return !$isCurrentlySubscriptionCart;
    }

    public function getConstraints(): array
    {
        return [
            'isSubscriptionCart' => RuleConstraints::bool(true),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->booleanField('isSubscriptionCart');
    }
}

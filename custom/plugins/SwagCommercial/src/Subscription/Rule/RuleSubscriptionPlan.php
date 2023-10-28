<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Rule;

use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleComparison;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

#[Package('checkout')]
class RuleSubscriptionPlan extends Rule
{
    final public const RULE_NAME = 'subscriptionPlan';

    /**
     * @internal
     *
     * @param list<string>|null $planIds
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?array $planIds = null
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$extension = $scope->getSalesChannelContext()->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        /** @var SubscriptionContextStruct $extension */
        $planId = $extension->getPlan()->getId();
        $parameter = $planId === '' ? [] : [$planId];

        return RuleComparison::uuids($parameter, $this->planIds, $this->operator);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConstraints(): array
    {
        return [
            'operator' => RuleConstraints::uuidOperators(),
            'planIds' => RuleConstraints::uuids(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('planIds', SubscriptionPlanDefinition::ENTITY_NAME, true);
    }
}

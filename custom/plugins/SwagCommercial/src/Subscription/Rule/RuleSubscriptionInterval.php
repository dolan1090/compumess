<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Rule;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleComparison;
use Shopware\Core\Framework\Rule\RuleConfig;
use Shopware\Core\Framework\Rule\RuleConstraints;
use Shopware\Core\Framework\Rule\RuleScope;

#[Package('checkout')]
class RuleSubscriptionInterval extends Rule
{
    final public const RULE_NAME = 'subscriptionInterval';

    /**
     * @internal
     *
     * @param list<string>|null $intervalIds
     */
    public function __construct(
        protected string $operator = self::OPERATOR_EQ,
        protected ?array $intervalIds = null
    ) {
        parent::__construct();
    }

    public function match(RuleScope $scope): bool
    {
        if (!$extension = $scope->getSalesChannelContext()->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            return RuleComparison::isNegativeOperator($this->operator);
        }

        /** @var SubscriptionContextStruct $extension */
        $intervalId = $extension->getInterval()->getId();
        $parameter = $intervalId === '' ? [] : [$intervalId];

        return RuleComparison::uuids($parameter, $this->intervalIds, $this->operator);
    }

    /**
     * @return array<string, mixed>
     */
    public function getConstraints(): array
    {
        return [
            'operator' => RuleConstraints::uuidOperators(),
            'intervalIds' => RuleConstraints::uuids(),
        ];
    }

    public function getConfig(): RuleConfig
    {
        return (new RuleConfig())
            ->operatorSet(RuleConfig::OPERATOR_SET_STRING, false, true)
            ->entitySelectField('intervalIds', SubscriptionIntervalDefinition::ENTITY_NAME, true);
    }
}

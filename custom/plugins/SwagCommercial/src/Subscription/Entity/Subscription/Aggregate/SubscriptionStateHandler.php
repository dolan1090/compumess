<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Entity\Subscription\Aggregate;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStateActions;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateCollection;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionStateHandler
{
    public function __construct(
        private readonly StateMachineRegistry $stateMachineRegistry
    ) {
    }

    public function activate(string $subscriptionId, Context $context): StateMachineStateCollection
    {
        return $this->stateMachineRegistry->transition(
            new Transition(
                SubscriptionDefinition::ENTITY_NAME,
                $subscriptionId,
                SubscriptionStateActions::ACTION_ACTIVATE,
                'stateId'
            ),
            $context
        );
    }

    public function pause(string $subscriptionId, Context $context): StateMachineStateCollection
    {
        return $this->stateMachineRegistry->transition(
            new Transition(
                SubscriptionDefinition::ENTITY_NAME,
                $subscriptionId,
                SubscriptionStateActions::ACTION_PAUSE,
                'stateId'
            ),
            $context
        );
    }

    public function cancel(string $subscriptionId, Context $context): StateMachineStateCollection
    {
        return $this->stateMachineRegistry->transition(
            new Transition(
                SubscriptionDefinition::ENTITY_NAME,
                $subscriptionId,
                SubscriptionStateActions::ACTION_CANCEL,
                'stateId'
            ),
            $context
        );
    }

    public function flagForCancellation(string $subscriptionId, Context $context): StateMachineStateCollection
    {
        return $this->stateMachineRegistry->transition(
            new Transition(
                SubscriptionDefinition::ENTITY_NAME,
                $subscriptionId,
                SubscriptionStateActions::ACTION_FLAG_FOR_CANCELLATION,
                'stateId'
            ),
            $context
        );
    }

    public function failPayment(string $subscriptionId, Context $context): StateMachineStateCollection
    {
        return $this->stateMachineRegistry->transition(
            new Transition(
                SubscriptionDefinition::ENTITY_NAME,
                $subscriptionId,
                SubscriptionStateActions::ACTION_FAIL_PAYMENT,
                'stateId'
            ),
            $context
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionStateHandler;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\StateMachine\StateMachineException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class SubscriptionCancelRoute extends AbstractSubscriptionCancelRoute
{
    /**
     * @internal
     *
     * * @param EntityRepository<SubscriptionCollection> $subscriptionRepository
     */
    public function __construct(
        private readonly SubscriptionStateHandler $subscriptionStateHandler,
        private readonly EntityRepository $subscriptionRepository
    ) {
    }

    public function getDecorated(): AbstractSubscriptionCancelRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/subscription/{subscriptionId}/cancel',
        name: 'store-api.subscription.state.cancel',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'SUBSCRIPTIONS-2437281\')'
    )]
    public function cancel(Request $request, SalesChannelContext $context, string $subscriptionId): SubscriptionStateResponse
    {
        $criteria = new Criteria([$subscriptionId]);

        $results = $this->subscriptionRepository->search($criteria, $context->getContext());

        /** @var SubscriptionEntity|null $subscription */
        $subscription = $results->first();

        $isRemainingExecution = $subscription?->getRemainingExecutionCount() > 0;

        $states = $isRemainingExecution
            ? $this->subscriptionStateHandler->flagForCancellation($subscriptionId, $context->getContext())
            : $this->subscriptionStateHandler->cancel($subscriptionId, $context->getContext());

        if (!$states->has('toPlace')) {
            $state = $isRemainingExecution
                ? SubscriptionStates::STATE_FLAGGED_CANCELLED
                : SubscriptionStates::STATE_CANCELLED;

            throw StateMachineException::stateMachineStateNotFound('subscription', $state);
        }

        return new SubscriptionStateResponse($states->get('toPlace'));
    }
}

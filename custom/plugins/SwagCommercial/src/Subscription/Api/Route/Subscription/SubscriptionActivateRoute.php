<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionStateHandler;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\StateMachine\StateMachineException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class SubscriptionActivateRoute extends AbstractSubscriptionActivateRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly SubscriptionStateHandler $subscriptionStateHandler)
    {
    }

    public function getDecorated(): AbstractSubscriptionActivateRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/subscription/{subscriptionId}/activate',
        name: 'store-api.subscription.state.activate',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'SUBSCRIPTIONS-2437281\')'
    )]
    public function activate(Request $request, SalesChannelContext $context, string $subscriptionId): SubscriptionStateResponse
    {
        $states = $this->subscriptionStateHandler->activate($subscriptionId, $context->getContext());

        if (!$states->has('toPlace')) {
            throw StateMachineException::stateMachineStateNotFound('subscription', SubscriptionStates::STATE_ACTIVE);
        }

        return new SubscriptionStateResponse($states->get('toPlace'));
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Interval\IntervalCalculator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\System\StateMachine\StateMachineException;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 *
 * @phpstan-ignore-next-line
 */
#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['api']])]
class AdminController
{
    public function __construct(
        private readonly IntervalCalculator $intervalCalculator,
        private readonly StateMachineRegistry $stateMachineRegistry,
    ) {
    }

    #[Route(
        path: '/api/_action/subscription/interval/generate-preview',
        name: 'commercial.api.action.subscription.interval.generate-preview',
        defaults: ['_acl' => ['subscription_interval:read']],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'SUBSCRIPTIONS-2437281\')'
    )]
    public function generateIntervalPreview(Request $request): JsonResponse
    {
        $cronInterval = $request->request->get('cronInterval');
        $dateInterval = $request->request->get('dateInterval');
        $timestamp = $request->request->getInt('timestamp', -1);
        $limit = $request->request->getInt('limit', 3);

        $date = new \DateTime();

        if ($timestamp >= 0) {
            $date->setTimestamp($timestamp);
        }

        if (!\is_string($cronInterval)) {
            throw RoutingException::missingRequestParameter('cronInterval');
        }
        if (!\is_string($dateInterval)) {
            throw RoutingException::missingRequestParameter('dateInterval');
        }

        $interval = new SubscriptionIntervalEntity();
        $interval->setCronInterval(new CronInterval($cronInterval));
        $interval->setDateInterval(new DateInterval($dateInterval));

        $dates = $this->intervalCalculator->getMultipleRunDates($limit, $interval, $timestamp < 0, $date);

        return new JsonResponse([
            'timestamps' => array_map(function ($date) {
                return $date->getTimestamp();
            }, $dates),
        ]);
    }

    #[Route(
        path: '/api/_action/subscription/{subscriptionId}/state/{transition}',
        name: 'api.action.subscription.state_machine.subscription.transition_state',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'SUBSCRIPTIONS-2437281\')'
    )]
    public function subscriptionStateTransition(
        string $subscriptionId,
        string $transition,
        Request $request,
        Context $context
    ): JsonResponse {
        $stateMachineStates = $this->stateMachineRegistry->transition(
            new Transition(
                'subscription',
                $subscriptionId,
                $transition,
                'stateId',
            ),
            $context
        );

        $toPlace = $stateMachineStates->get('toPlace');

        if (!$toPlace) {
            throw StateMachineException::stateMachineStateNotFound('subscription', $transition);
        }

        return new JsonResponse($toPlace->jsonSerialize());
    }
}

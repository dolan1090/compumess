<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Routing\Mapping;

use Shopware\Commercial\Subscription\Checkout\Cart\SubscriptionCartException;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanEntity;
use Shopware\Commercial\Subscription\Framework\Routing\SubscriptionRequest;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Commercial\Subscription\Interval\IntervalCalculator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Shopware\Core\PlatformRequest;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class SubscriptionContextStructBuilder
{
    /**
     * @internal
     *
     * @param EntityRepository<SubscriptionPlanCollection> $planRepository
     */
    public function __construct(
        private readonly EntityRepository $planRepository,
        private readonly SubscriptionCartMappingResolver $cartMappingResolver,
        private readonly IntervalCalculator $intervalCalculator
    ) {
    }

    public function buildFromRequest(Request $request, Context $context): SubscriptionContextStruct
    {
        $intervalId = $request->headers->get(SubscriptionRequest::HEADER_SUBSCRIPTION_INTERVAL);
        $planId = $request->headers->get(SubscriptionRequest::HEADER_SUBSCRIPTION_PLAN);

        if (!\is_string($intervalId)) {
            throw RoutingException::missingRequestParameter('Interval id');
        }

        if (!\is_string($planId)) {
            throw RoutingException::missingRequestParameter('Plan id');
        }

        $contextToken = $request->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);

        if ($contextToken === null) {
            throw RoutingException::missingRequestParameter('Context token');
        }

        ['plan' => $plan, 'interval' => $interval, 'subscriptionToken' => $subscriptionToken] = $this->loadPlanAndInterval($planId, $intervalId, $context, $contextToken);

        $nextSchedule = $this->intervalCalculator->getInitialRunDate($interval);

        return new SubscriptionContextStruct($contextToken, $nextSchedule, $interval, $plan, $subscriptionToken);
    }

    /**
     * @return array{interval: SubscriptionIntervalEntity, plan: SubscriptionPlanEntity, subscriptionToken: string|null}
     */
    private function loadPlanAndInterval(string $planId, string $intervalId, Context $context, string $contextToken): array
    {
        $criteria = new Criteria([$planId]);
        $criteria->addFilter(new EqualsFilter('subscriptionIntervals.id', $intervalId));
        $criteria->getAssociation('subscriptionIntervals')->addFilter(new EqualsFilter('id', $intervalId));

        /** @var SubscriptionPlanEntity|null $plan */
        $plan = $this->planRepository->search($criteria, $context)->first();

        if ($plan === null) {
            throw SubscriptionCartException::planNotFound($planId);
        }

        $interval = $plan->getSubscriptionIntervals()?->first();

        if ($interval === null) {
            throw SubscriptionCartException::intervalNotFound($intervalId);
        }

        $token = $this->cartMappingResolver->getSubscriptionToken($intervalId, $planId, $contextToken);

        return ['plan' => $plan, 'interval' => $interval, 'subscriptionToken' => $token];
    }
}

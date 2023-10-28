<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class SubscriptionRoute extends AbstractSubscriptionRoute
{
    /**
     * @param EntityRepository<SubscriptionCollection> $subscriptionRepository
     *
     * @internal
     */
    public function __construct(private readonly EntityRepository $subscriptionRepository)
    {
    }

    public function getDecorated(): AbstractSubscriptionRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/subscription',
        name: 'store-api.subscription',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_entity' => 'subscription'],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'SUBSCRIPTIONS-2437281\')'
    )]
    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): SubscriptionRouteResponse
    {
        $criteria->addFilter(new EqualsFilter('subscriptionCustomer.customerId', $context->getCustomer()?->getId()));

        $result = $this->subscriptionRepository->search($criteria, $context->getContext());

        return new SubscriptionRouteResponse($result);
    }
}

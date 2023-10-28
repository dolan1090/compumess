<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront\Page\Account\Subscription;

use Shopware\Commercial\Subscription\Api\Route\Subscription\AbstractSubscriptionRoute;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Do not use direct or indirect repository calls in a PageLoader. Always use a store-api route to get or put data.
 */
#[Package('checkout')]
class AccountSubscriptionPageLoader
{
    /**
     * @internal
     */
    public function __construct(
        private readonly GenericPageLoaderInterface $genericLoader,
        private readonly AbstractSubscriptionRoute $subscriptionRoute,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function load(Request $request, SalesChannelContext $salesChannelContext): AccountSubscriptionPage
    {
        if (!$salesChannelContext->getCustomer()) {
            throw CartException::customerNotLoggedIn();
        }

        $page = $this->genericLoader->load($request, $salesChannelContext);

        $page = AccountSubscriptionPage::createFrom($page);

        $page->getMetaInformation()?->setRobots('noindex,follow');

        $page->setSubscriptions(StorefrontSearchResult::createFrom($this->getSubscriptions($request, $salesChannelContext)));

        $this->eventDispatcher->dispatch(
            new AccountSubscriptionPageLoadedEvent($page, $salesChannelContext, $request)
        );

        return $page;
    }

    /**
     * @return EntitySearchResult<SubscriptionCollection>
     */
    private function getSubscriptions(Request $request, SalesChannelContext $context): EntitySearchResult
    {
        $criteria = $this->createCriteria($request);

        $responseStruct = $this->subscriptionRoute->load($request, $context, $criteria);

        return $responseStruct->getSubscriptions();
    }

    private function createCriteria(Request $request): Criteria
    {
        $limit = $request->request->getInt('limit', 10);
        $page = $request->request->getInt('p', 1);

        $subscriptionId = $request->attributes->getAlnum('subscriptionId')
                ?: $request->request->getAlnum('subscriptionId')
                ?: $request->query->getAlnum('subscriptionId');

        $subscriptionId = empty($subscriptionId) ? null : [$subscriptionId];

        return (new Criteria($subscriptionId))
            ->addSorting(new FieldSorting('subscription.createdAt', FieldSorting::DESCENDING))
            ->setLimit($limit)
            ->addAssociation('subscriptionInterval')
            ->addAssociation('stateMachineState')
            ->addAssociation('paymentMethod')
            ->addAssociation('shippingMethod')
            ->addAssociation('currency')
            ->setOffset(($page - 1) * $limit)
            ->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);
    }
}

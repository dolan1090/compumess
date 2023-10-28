<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront\Controller;

use Shopware\Commercial\Subscription\Api\Route\Subscription\AbstractSubscriptionActivateRoute;
use Shopware\Commercial\Subscription\Api\Route\Subscription\AbstractSubscriptionCancelRoute;
use Shopware\Commercial\Subscription\Api\Route\Subscription\AbstractSubscriptionPauseRoute;
use Shopware\Commercial\Subscription\Storefront\Page\Account\Subscription\AccountSubscriptionPageLoadedHook;
use Shopware\Commercial\Subscription\Storefront\Page\Account\Subscription\AccountSubscriptionPageLoader;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 * Do not use direct or indirect repository calls in a controller. Always use a store-api route to get or put data
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('storefront')]
class AccountSubscriptionController extends StorefrontController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractSubscriptionActivateRoute $subscriptionActivateRoute,
        private readonly AbstractSubscriptionCancelRoute $subscriptionCancelRoute,
        private readonly AbstractSubscriptionPauseRoute $subscriptionPauseRoute,
        private readonly AccountSubscriptionPageLoader $subscriptionPageLoader,
    ) {
    }

    #[Route(path: '/account/subscription', name: 'frontend.account.subscription.page', options: ['seo' => false], defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_noStore' => true], methods: ['GET', 'POST'], condition: 'service(\'license\').check(\'SUBSCRIPTIONS-6549379\')')]
    public function subscriptionOverview(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->subscriptionPageLoader->load($request, $context);

        $this->hook(new AccountSubscriptionPageLoadedHook($page, $context));

        return $this->renderStorefront('@Commercial/storefront/page/account/subscription/index.html.twig', ['page' => $page]);
    }

    #[Route(path: '/account/subscription/detail/{subscriptionId}', name: 'frontend.account.subscription.detail.page', options: ['seo' => false], defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_noStore' => true], methods: ['GET', 'POST'], condition: 'service(\'license\').check(\'SUBSCRIPTIONS-6549379\')')]
    public function subscriptionDetail(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->subscriptionPageLoader->load($request, $context);

        $this->hook(new AccountSubscriptionPageLoadedHook($page, $context));

        return $this->renderStorefront('@Commercial/storefront/page/account/subscription/index.html.twig', ['page' => $page]);
    }

    #[Route(path: '/account/subscription/{subscriptionId}/cancel', name: 'frontend.account.subscription.cancel', options: ['seo' => false], defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function subscriptionCancel(Request $request, SalesChannelContext $context, string $subscriptionId): Response
    {
        $this->subscriptionCancelRoute->cancel($request, $context, $subscriptionId);

        return $this->redirectToRoute('frontend.account.subscription.page');
    }

    #[Route(path: '/account/subscription/{subscriptionId}/pause', name: 'frontend.account.subscription.pause', options: ['seo' => false], defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function subscriptionPause(Request $request, SalesChannelContext $context, string $subscriptionId): Response
    {
        $this->subscriptionPauseRoute->pause($request, $context, $subscriptionId);

        return $this->redirectToRoute('frontend.account.subscription.page');
    }

    #[Route(path: '/account/subscription/{subscriptionId}/activate', name: 'frontend.account.subscription.activate', options: ['seo' => false], defaults: ['XmlHttpRequest' => true, '_loginRequired' => true, '_loginRequiredAllowGuest' => true, '_noStore' => true], methods: ['GET', 'POST'])]
    public function subscriptionActivate(Request $request, SalesChannelContext $context, string $subscriptionId): Response
    {
        $this->subscriptionActivateRoute->activate($request, $context, $subscriptionId);

        return $this->redirectToRoute('frontend.account.subscription.page');
    }
}

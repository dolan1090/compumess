<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Storefront;

use Shopware\Commercial\B2B\QuickOrder\Domain\CustomerSpecificFeature\CustomerSpecificFeatureService;
use Shopware\Commercial\B2B\QuickOrder\Exception\CustomerSpecificFeatureException;
use Shopware\Commercial\B2B\QuickOrder\Page\AccountQuickOrderPageLoader;
use Shopware\Commercial\B2B\QuickOrder\QuickOrder;
use Shopware\Commercial\B2B\QuickOrder\SalesChannel\Account\AbstractQuickOrderProcessFileRoute;
use Shopware\Commercial\B2B\QuickOrder\SalesChannel\Account\AbstractQuickOrderSearchProductRoute;
use Shopware\Core\Content\Product\SalesChannel\ProductListResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('checkout')]
class AccountQuickOrderController extends StorefrontController
{
    private const LIMIT = 10;

    private const DEFAULT_PAGE = 1;

    /**
     * @internal
     */
    public function __construct(
        private readonly AccountQuickOrderPageLoader $accountQuickOrderPageLoader,
        private readonly CustomerSpecificFeatureService $customerSpecificFeatureService,
        private readonly AbstractQuickOrderSearchProductRoute $quickOrderSearchProductRoute,
        private readonly AbstractQuickOrderProcessFileRoute $quickOrderProcessFileRoute,
    ) {
    }

    #[Route(
        path: '/account/quick-order',
        name: 'frontend.account.quick-order.page',
        defaults: ['_noStore' => false, '_loginRequired' => true],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'QUICK_ORDER-9771104\')'
    )]
    public function quickOrder(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        if (!$this->isAllowed($salesChannelContext->getCustomerId())) {
            throw CustomerSpecificFeatureException::notAllowed(QuickOrder::CODE);
        }

        $page = $this->accountQuickOrderPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront('@SwagQuickOrder/storefront/page/account/quick-order/index.html.twig', ['page' => $page]);
    }

    #[Route(
        path: '/account/quick-order/product/suggest',
        name: 'frontend.account.quick-order.product.suggest',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true, '_noStore' => false, '_loginRequired' => true],
        methods: ['GET'],
        condition: 'service(\'license\').check(\'QUICK_ORDER-9771104\')'
    )]
    public function suggest(SalesChannelContext $context, Request $request): ProductListResponse
    {
        if (!$this->isAllowed($context->getCustomerId())) {
            throw CustomerSpecificFeatureException::notAllowed(QuickOrder::CODE);
        }

        $limit = $request->query->getInt('limit', self::LIMIT);
        $page = $request->query->getInt('page', self::DEFAULT_PAGE);

        $criteria = new Criteria();
        $criteria->setLimit($limit);
        $criteria->setOffset(($page - 1) * $criteria->getLimit());
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        $response = $this->quickOrderSearchProductRoute->suggest($context, $request, $criteria);
        $response->headers->set('x-robots-tag', 'noindex');

        return $response;
    }

    #[Route(
        path: '/account/quick-order/upload',
        name: 'frontend.account.quick-order.upload',
        defaults: ['XmlHttpRequest' => true, '_noStore' => false, '_loginRequired' => true],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'QUICK_ORDER-9771104\')'
    )]
    public function loadProductsFromFile(SalesChannelContext $context, Request $request): JsonResponse
    {
        if (!$this->isAllowed($context->getCustomerId())) {
            throw CustomerSpecificFeatureException::notAllowed(QuickOrder::CODE);
        }

        return $this->quickOrderProcessFileRoute->load($request, $context);
    }

    private function isAllowed(?string $customerId): bool
    {
        return $this->customerSpecificFeatureService->isAllowed($customerId, QuickOrder::CODE);
    }
}

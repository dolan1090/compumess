<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\SalesChannel\Account;

use Shopware\Commercial\B2B\QuickOrder\Domain\CustomerSpecificFeature\CustomerSpecificFeatureService;
use Shopware\Commercial\B2B\QuickOrder\Exception\CustomerSpecificFeatureException;
use Shopware\Commercial\B2B\QuickOrder\QuickOrder;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductListRoute;
use Shopware\Core\Content\Product\SalesChannel\ProductListResponse;
use Shopware\Core\Content\Product\SearchKeyword\ProductSearchBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class QuickOrderSearchProductRoute extends AbstractQuickOrderSearchProductRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ProductSearchBuilderInterface $searchBuilder,
        private readonly AbstractProductListRoute $productListRoute,
        private readonly CustomerSpecificFeatureService $customerSpecificFeatureService,
    ) {
    }

    public function getDecorated(): AbstractQuickOrderSearchProductRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/quick-order/product',
        name: 'store-api.quick-order.product',
        defaults: ['_entity' => 'quick-order'],
        methods: ['GET', 'POST'],
        condition: 'service(\'license\').check(\'QUICK_ORDER-9771104\')'
    )]
    public function suggest(SalesChannelContext $context, Request $request, Criteria $criteria): ProductListResponse
    {
        if (!$this->customerSpecificFeatureService->isAllowed($context->getCustomerId(), QuickOrder::CODE)) {
            throw CustomerSpecificFeatureException::notAllowed(QuickOrder::CODE);
        }

        $criteria->addAssociation('options.group');
        $criteria->addState(Criteria::STATE_ELASTICSEARCH_AWARE);

        $criteria->addFilter(
            new NotFilter(
                MultiFilter::CONNECTION_AND,
                [new EqualsFilter('displayGroup', null)]
            )
        );

        $this->searchBuilder->build($request, $criteria, $context);

        return $this->productListRoute->load($criteria, $context);
    }
}

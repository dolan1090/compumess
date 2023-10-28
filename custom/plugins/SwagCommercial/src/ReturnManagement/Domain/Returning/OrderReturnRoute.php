<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnStates;
use Shopware\Commercial\ReturnManagement\Event\OrderReturnCreatedEvent;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type ReturnItemData from OrderReturnLineItemFactory
 * @phpstan-import-type RequestReturnItem from OrderReturnLineItemFactory
 *
 * This route is used to create a return of the logged-in customer
 */
#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class OrderReturnRoute extends AbstractOrderReturnRoute
{
    final public const ALLOW_CREATE_RETURN_ON_ANY_ORDERS = 'allowCreateReturnOnAnyOrders';
    private const LINE_ITEMS_PROPERTY = 'lineItems';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $orderReturnRepository,
        private readonly OrderReturnLineItemFactory $orderReturnLineItemFactory,
        private readonly OrderReturnRouteValidator $returnRouteValidator,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly InitialStateIdLoader $initialStateIdLoader,
        private readonly PositionStateHandler $positionStateHandler,
        private readonly AmountCalculator $amountCalculator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function getDecorated(): AbstractOrderReturnRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @Since("6.5.0.0")
     */
    #[Route(
        path: '/store-api/order/{orderId}/return',
        name: 'store-api.order.return',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')',
    )]
    public function return(string $orderId, Request $request, SalesChannelContext $context): ?OrderReturnRouteResponse
    {
        $this->returnRouteValidator->validateRequest($request, $orderId, $context);

        /** @var array<RequestReturnItem> $requestItems */
        $requestItems = $request->get(self::LINE_ITEMS_PROPERTY);

        /** @var string|null $internalComment */
        $internalComment = $request->get('internalComment');

        $returnId = $this->create(
            $requestItems,
            $orderId,
            $context,
            ['internalComment' => $internalComment]
        );

        $criteria = new Criteria([$returnId]);
        $criteria->addAssociation('order.orderCustomer');
        /** @var OrderReturnEntity $return */
        $return = $this->orderReturnRepository->search($criteria, $context->getContext())->first();

        $this->eventDispatcher->dispatch(new OrderReturnCreatedEvent($return, $context));

        return new OrderReturnRouteResponse($return);
    }

    /**
     * @param array<RequestReturnItem> $requestLineItems
     * @param array<string, mixed> $options
     */
    private function create(array $requestLineItems, string $orderId, SalesChannelContext $context, array $options = []): string
    {
        $returnLineItems = $this->orderReturnLineItemFactory->createProducts(
            $requestLineItems,
            null,
            $context
        );

        /** @var CalculatedPrice[] $returnItemPrices */
        $returnItemPrices = array_map(static fn (array $returnLineItem) => $returnLineItem['price'], $returnLineItems);

        $returnPrice = $this->amountCalculator->calculate(new PriceCollection($returnItemPrices), new PriceCollection([new CalculatedPrice(0, 0, new CalculatedTaxCollection(), new TaxRuleCollection())]), $context);

        $returnId = Uuid::randomHex();

        $returnData = array_merge([
            'id' => $returnId,
            'amountTotal' => $returnPrice->getTotalPrice(),
            'amountNet' => $returnPrice->getNetPrice(),
            'price' => $returnPrice,
            'orderId' => $orderId,
            'orderVersionId' => $context->getVersionId(),
            'returnNumber' => $this->numberRangeValueGenerator->getValue(
                OrderReturnDefinition::ENTITY_NAME,
                $context->getContext(),
                $context->getSalesChannel()->getId()
            ),
            'lineItems' => $returnLineItems,
            'shippingCosts' => new CalculatedPrice(0, 0, new CalculatedTaxCollection(), $returnPrice->getTaxRules()),
            'stateId' => $this->initialStateIdLoader->get(OrderReturnStates::STATE_MACHINE),
            'requestedAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ], $options);

        $this->orderReturnRepository->create([$returnData], $context->getContext());

        /** @var array<string> $itemIds */
        $itemIds = array_column($returnLineItems, 'orderLineItemId');

        $this->positionStateHandler->transitOrderLineItems($itemIds, PositionStateHandler::STATE_RETURN_REQUESTED, $context->getContext());

        return $returnId;
    }
}

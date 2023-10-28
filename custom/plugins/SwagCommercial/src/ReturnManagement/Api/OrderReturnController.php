<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Api;

use Doctrine\DBAL\Connection;
use OpenApi\Annotations as OA;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnCalculator;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnLineItemFactory;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Domain\Validation\AbstractOrderReturnValidationFactory;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemAllowedTypes;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type RequestReturnItem from OrderReturnLineItemFactory
 */
#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('checkout')]
class OrderReturnController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly PositionStateHandler $positionStateHandler,
        private readonly Connection $connection,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SalesChannelContextRestorer $contextRestorer,
        private readonly DataValidator $validator,
        private readonly AbstractOrderReturnValidationFactory $returnValidationFactory,
        private readonly EntityRepository $orderLineItemRepository,
        private readonly EntityRepository $orderReturnLineItemRepository,
        private readonly OrderReturnLineItemFactory $orderReturnLineItemFactory,
        private readonly OrderReturnCalculator $returnCalculator
    ) {
    }

    /**
     * @Since("6.5.0.0")
     *
     * @OA\Post(
     *     path="/api/_action/order/{orderId}/order-return/{orderReturnId}/add-items",
     *     summary="Add items to order return",
     *     description="The merchant is able to add items to an existing return.",
     *     operationId="addItemsToReturn",
     *     tags={"Admin API", "Order Return Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                 "orderLineItems",
     *             },
     *
     *             @OA\Property(
     *                  required={
     *                      "orderLineItemId",
     *                      "quantity",
     *                  },
     *                 property="orderReturnLineItems",
     *                 description="A list of the order line items, which need to be added to return",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                  @OA\Property(property="orderLineItemId", type="string"),
     *                  @OA\Property(property="quantity", type="integer"),
     *                  @OA\Property(property="internalComment", type="string"),
     *                 )
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The list of items added to return succesful."
     *     )
     * )
     */
    #[Route(
        path: '/api/_action/order/{orderId}/order-return/{orderReturnId}/add-items',
        name: 'api.action.order.order_return.add-items',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')'
    )]
    public function addItems(string $orderId, string $orderReturnId, Request $request, Context $context): Response
    {
        $this->validate($orderId, $orderReturnId, $request, $context);

        /** @var array<RequestReturnItem> $requestLineItems */
        $requestLineItems = $request->get('orderLineItems');

        $returnLineItems = $this->orderReturnLineItemFactory->createProducts(
            $requestLineItems,
            $orderReturnId,
            $this->contextRestorer->restoreByOrder($orderId, $context)
        );

        $this->orderReturnLineItemRepository->create($returnLineItems, $context);
        $this->returnCalculator->calculate($orderReturnId, $context);

        /** @var array<string> $paramsOrderLineItemsIds */
        $paramsOrderLineItemsIds = array_column($returnLineItems, 'orderLineItemId');
        $this->positionStateHandler->transitOrderLineItems($paramsOrderLineItemsIds, PositionStateHandler::STATE_RETURN_REQUESTED, $context);

        return new NoContentResponse();
    }

    private function validate(string $orderId, string $orderReturnId, Request $request, Context $context): void
    {
        $requestData = $request->request->all();

        $requestData['orderId'] = $orderId;
        $requestData['orderReturnId'] = $orderReturnId;
        $definition = $this->returnValidationFactory->addItems($orderId, $context);
        $validationEvent = new BuildValidationEvent($definition, new DataBag($requestData), $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());
        $violations = $this->validator->getViolations($requestData, $definition);

        $orderLineItemsIds = $this->getOrderLineItemsIds($orderReturnId, $context->getVersionId());

        /** @var array<RequestReturnItem> $requestItems */
        $requestItems = $requestData['orderLineItems'];
        $itemsIds = [];
        foreach ($requestItems as $requestItem) {
            $itemsIds[] = $requestItem['orderLineItemId'];
        }

        $criteria = new Criteria($itemsIds);
        $criteria->addFilter(new EqualsAnyFilter('type', OrderReturnLineItemAllowedTypes::LINE_ITEM_TYPES));

        /** @var OrderLineItemCollection $orderLineItems */
        $orderLineItems = $this->orderLineItemRepository->search($criteria, $context)->getEntities();
        foreach ($requestItems as $index => $requestItem) {
            $requestLineItemId = $requestItem['orderLineItemId'];
            if (\in_array($requestLineItemId, $orderLineItemsIds, true)) {
                $violations->add($this->returnValidationFactory->buildConstraintViolation(
                    'This order line item is already part of the return',
                    implode('/', ['orderLineItems', $index, 'orderLineItemId']),
                    $requestLineItemId,
                    AbstractOrderReturnValidationFactory::ERROR_CODE_DUPLICATE_ORDER_RETURN_LINE_ITEM
                ));

                continue;
            }

            /** @var OrderLineItemEntity $orderLineItem */
            $orderLineItem = $orderLineItems->get($requestLineItemId);
            $requestQuantity = $requestItem['quantity'];
            if ($requestQuantity <= $orderLineItem->getQuantity()) {
                continue;
            }

            $violations->add($this->returnValidationFactory->buildConstraintViolation(
                'Quantity should be lesser or equal line item quantity',
                implode('/', ['orderLineItems', $index, 'quantity']),
                $requestQuantity,
                AbstractOrderReturnValidationFactory::ERROR_CODE_INVALID_RETURN_LINE_ITEM_QUANTITY
            ));
        }

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $requestData);
        }
    }

    /**
     * @return array<string>
     */
    private function getOrderLineItemsIds(string $orderReturnId, string $versionId): array
    {
        $results = $this->connection->fetchAllAssociative(
            'SELECT LOWER(HEX(`order_line_item_id`)) `order_line_item_id` FROM `order_return_line_item`
                        WHERE `order_return_line_item`.`order_return_id` = :return_id AND `order_line_item_version_id` = :version_id',
            [
                'return_id' => Uuid::fromHexToBytes($orderReturnId),
                'version_id' => Uuid::fromHexToBytes($versionId),
            ]
        );

        /** @var array<string> $results */
        $results = array_column($results, 'order_line_item_id');

        return $results;
    }
}

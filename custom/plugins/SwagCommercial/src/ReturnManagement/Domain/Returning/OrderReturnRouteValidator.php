<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Domain\Validation\AbstractOrderReturnValidationFactory;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemAllowedTypes;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Api\Controller\Exception\PermissionDeniedException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\DataBag\DataBag;
use Shopware\Core\Framework\Validation\DataValidator;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @phpstan-import-type RequestReturnItem from OrderReturnLineItemFactory
 */
#[Package('checkout')]
class OrderReturnRouteValidator
{
    private const LINE_ITEMS_PROPERTY = 'lineItems';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $orderLineItemRepository,
        private readonly AbstractOrderReturnValidationFactory $returnValidationFactory,
        private readonly DataValidator $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection
    ) {
    }

    public function validateRequest(Request $request, string $orderId, SalesChannelContext $context): void
    {
        $requestData = $request->request->all();

        /** @var array<RequestReturnItem> $requestItems */
        $requestItems = $requestData[self::LINE_ITEMS_PROPERTY];

        $definition = $this->returnValidationFactory->create($orderId, $context);
        $validationEvent = new BuildValidationEvent($definition, new DataBag($requestData), $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        $violations = $this->validator->getViolations($requestData, $definition);
        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $requestItems);
        }

        // Check permission whether user create return by their own (storefront) or merchant create it on admin
        if ($context->hasPermission(OrderReturnRoute::ALLOW_CREATE_RETURN_ON_ANY_ORDERS)) {
            $this->validateRequestForAdmin($violations, $requestItems, $context->getContext());
        } else {
            $this->validateRequestNonAdmin($violations, $orderId, $requestItems, $context);
        }

        if ($violations->count() > 0) {
            throw new ConstraintViolationException($violations, $requestItems);
        }
    }

    /**
     * @param array<RequestReturnItem> $requestItems
     */
    private function validateRequestForAdmin(ConstraintViolationList $violations, array $requestItems, Context $context): void
    {
        $paramsOrderLineItemsIds = [];
        foreach ($requestItems as $requestItem) {
            $paramsOrderLineItemsIds[] = $requestItem['orderLineItemId'];
        }

        $criteria = new Criteria($paramsOrderLineItemsIds);
        $criteria->addFilter(new EqualsAnyFilter('type', OrderReturnLineItemAllowedTypes::LINE_ITEM_TYPES));

        /** @var OrderLineItemCollection $orderLineItems */
        $orderLineItems = $this->orderLineItemRepository->search($criteria, $context)->getEntities();
        foreach ($requestItems as $index => $requestItem) {
            $requestItemId = $requestItem['orderLineItemId'];
            $requestItemQuantity = $requestItem['quantity'];
            /** @var OrderLineItemEntity|null $orderLineItem */
            $orderLineItem = $orderLineItems->get($requestItemId);
            $currentQuantity = $orderLineItem ? $orderLineItem->getQuantity() : 0;

            if ($requestItemQuantity <= $currentQuantity) {
                continue;
            }

            $violations->add($this->returnValidationFactory->buildConstraintViolation(
                'Quantity should be lesser or equal line item quantity',
                implode('/', [self::LINE_ITEMS_PROPERTY, $index, 'quantity']),
                $requestItemQuantity,
                AbstractOrderReturnValidationFactory::ERROR_CODE_INVALID_RETURN_LINE_ITEM_QUANTITY
            ));
        }
    }

    /**
     * @param array<RequestReturnItem> $requestItems
     */
    private function validateRequestNonAdmin(ConstraintViolationList $violations, string $orderId, array $requestItems, SalesChannelContext $context): void
    {
        $customer = $context->getCustomer();
        if ($customer && !$this->isOrderBelongToCustomer($orderId, $customer->getId())) {
            throw new PermissionDeniedException();
        }

        $requestItemIds = array_map(fn ($requestItem) => $requestItem['orderLineItemId'], $requestItems);

        $lineItemsQuantityMapping = $this->getOrderLineItemsQuantityByStates($requestItemIds, [
            PositionStateHandler::STATE_SHIPPED,
            PositionStateHandler::STATE_SHIPPED_PARTIALLY,
        ], $context->getContext());

        foreach ($requestItems as $index => $requestItem) {
            $currentQuantity = $lineItemsQuantityMapping[$requestItem['orderLineItemId']] ?? 0;
            $requestItemQuantity = $requestItem['quantity'];
            if (!$currentQuantity) {
                $violations->add($this->returnValidationFactory->buildConstraintViolation(
                    'Invalid order line item\'s state',
                    implode('/', [self::LINE_ITEMS_PROPERTY, $index, 'stateId']),
                    null,
                    AbstractOrderReturnValidationFactory::ERROR_CODE_INVALID_RETURN_LINE_ITEM_STATE
                ));

                continue;
            }

            if ($currentQuantity >= $requestItemQuantity) {
                continue;
            }

            $violations->add($this->returnValidationFactory->buildConstraintViolation(
                'Quantity should be lesser or equal line item quantity',
                implode('/', [self::LINE_ITEMS_PROPERTY, $index, 'quantity']),
                $requestItemQuantity,
                AbstractOrderReturnValidationFactory::ERROR_CODE_INVALID_RETURN_LINE_ITEM_QUANTITY
            ));
        }
    }

    private function isOrderBelongToCustomer(string $orderId, string $customerId): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT count(`id`) FROM `order_customer` WHERE `order_id` = :order_id AND `customer_id` = :customer_id',
            [
                'order_id' => Uuid::fromHexToBytes($orderId),
                'customer_id' => Uuid::fromHexToBytes($customerId),
            ]
        );
    }

    /**
     * @param string[] $ids
     * @param string[] $stateTechnicalNames
     *
     * @return array<string, int>
     */
    private function getOrderLineItemsQuantityByStates(array $ids, array $stateTechnicalNames, Context $context): array
    {
        /** @var array<string, int> */
        return $this->connection->fetchAllKeyValue(
            'SELECT LOWER(HEX(`order_line_item`.`id`)) `id`, `order_line_item`.`quantity` `quantity`
                        FROM `order_line_item`
                        INNER JOIN `state_machine_state` ON `state_machine_state`.`id` = `order_line_item`.`state_id`
                        INNER JOIN `state_machine` ON `state_machine`.`id` = `state_machine_state`.`state_machine_id`
                    WHERE `order_line_item`.`id` IN (:order_line_item_ids)
                      AND `order_line_item`.`type` IN (:types)
                      AND `state_machine`.`technical_name` = :machine_technical_name
                      AND `state_machine_state`.`technical_name` IN (:technical_names)
                      AND `version_id` = :version_id',
            [
                'order_line_item_ids' => Uuid::fromHexToBytesList($ids),
                'types' => OrderReturnLineItemAllowedTypes::LINE_ITEM_TYPES,
                'machine_technical_name' => PositionStateHandler::STATE_MACHINE,
                'technical_names' => $stateTechnicalNames,
                'version_id' => Uuid::fromHexToBytes($context->getVersionId()),
            ],
            [
                'order_line_item_ids' => ArrayParameterType::STRING,
                'technical_names' => ArrayParameterType::STRING,
                'types' => ArrayParameterType::STRING,
            ]
        );
    }
}

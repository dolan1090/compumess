<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemAllowedTypes;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonDefinition;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 *
 * @phpstan-type ReturnItemData array{orderLineItemId: string, orderLineItemVersionId: string, quantity: int,
 *              internalComment: ?string, refundAmount: float, restockQuantity: int, price: ?CalculatedPrice,
 *              reasonId: string, stateId: string, orderReturnId?: string}
 * @phpstan-type RequestReturnItem array{orderLineItemId: string, quantity: int, internalComment?: string}
 */
#[Package('checkout')]
class OrderReturnLineItemFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $orderLineItemRepository,
        private readonly QuantityPriceCalculator $calculator,
        private readonly PositionStateHandler $positionStateHandler,
        private readonly Connection $connection
    ) {
    }

    /**
     * @internal
     *
     * @param array<RequestReturnItem> $requestLineItems
     *
     * @return array<ReturnItemData>
     */
    public function createProducts(array $requestLineItems, ?string $existingReturnId, SalesChannelContext $context): array
    {
        $lineItemIds = [];
        foreach ($requestLineItems as $requestLineItem) {
            $lineItemIds[] = $requestLineItem['orderLineItemId'];
        }

        $criteria = new Criteria($lineItemIds);
        $criteria->addFilter(new EqualsAnyFilter('type', OrderReturnLineItemAllowedTypes::LINE_ITEM_TYPES));

        /** @var OrderLineItemCollection $orderLineItems */
        $orderLineItems = $this->orderLineItemRepository->search($criteria, $context->getContext())->getEntities();

        $returnLineItems = [];
        $stateId = $this->positionStateHandler->getStateId(PositionStateHandler::STATE_RETURN_REQUESTED);
        $defaultReasonId = $this->getDefaultReturnLineItemReasonId();
        foreach ($requestLineItems as $requestLineItem) {
            $lineItemId = $requestLineItem['orderLineItemId'];
            /** @var OrderLineItemEntity $orderLineItem */
            $orderLineItem = $orderLineItems->get($lineItemId);
            $quantity = (int) $requestLineItem['quantity'];
            /** @var CalculatedPrice $price */
            $price = $orderLineItem->getPrice();
            $returnItemPrice = $this->calculator->calculate(new QuantityPriceDefinition($orderLineItem->getUnitPrice(), $price->getTaxRules(), $quantity), $context);

            $returnLineItem = [
                'orderLineItemId' => $lineItemId,
                'orderLineItemVersionId' => $context->getVersionId(),
                'quantity' => $quantity,
                'internalComment' => $requestLineItem['internalComment'] ?? null,
                'refundAmount' => $quantity * $returnItemPrice->getUnitPrice(),
                'restockQuantity' => 0,
                'price' => $returnItemPrice,
                'reasonId' => $requestLineItem['reasonId'] ?? $defaultReasonId,
                'stateId' => $stateId,
            ];
            if ($existingReturnId) {
                $returnLineItem['orderReturnId'] = $existingReturnId;
            }

            $returnLineItems[] = $returnLineItem;
        }

        return $returnLineItems;
    }

    private function getDefaultReturnLineItemReasonId(): string
    {
        /** @var string */
        return $this->connection->fetchOne(
            'SELECT LOWER(HEX(id)) FROM `order_return_line_item_reason` WHERE `reason_key` = :reason_key',
            [
                'reason_key' => OrderReturnLineItemReasonDefinition::DEFAULT_REASON_KEY,
            ]
        );
    }
}

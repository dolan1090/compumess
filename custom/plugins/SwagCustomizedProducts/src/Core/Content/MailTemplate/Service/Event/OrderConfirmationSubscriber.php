<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Content\MailTemplate\Service\Event;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateTypes;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\DateTime;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Timestamp;

class OrderConfirmationSubscriber
{
    public function __construct(private readonly EntityRepository $mailTemplateRepository)
    {
    }

    public function __invoke(MailBeforeValidateEvent $event): void
    {
        $data = $event->getData();
        if (!\array_key_exists('templateId', $data)) {
            return;
        }

        if (!$this->isRelevantMailTemplate($data['templateId'], $event->getContext())) {
            return;
        }

        $order = $event->getTemplateData()['order'];
        if ($order === null || !$order instanceof OrderEntity) {
            return;
        }

        $orderLineItemCollection = $order->getLineItems();
        if ($orderLineItemCollection === null) {
            return;
        }
        $orderLineItemCollection->sortByPosition();

        $customizedProductOptionValueLineItems = $orderLineItemCollection->filterByType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $this->adjustQuantityAndRemoveProductNumber($customizedProductOptionValueLineItems);
        $templateLineItems = $orderLineItemCollection->filterByType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        foreach ($templateLineItems as $templateLineItem) {
            $childLineItems = $orderLineItemCollection->filterByProperty('parentId', $templateLineItem->getId());
            $productLineItems = $childLineItems->filterByType(LineItem::PRODUCT_LINE_ITEM_TYPE);
            $customizedProductOptionLineItems = $childLineItems->filterByType(
                CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
            );

            $this->adjustQuantityAndRemoveProductNumber($customizedProductOptionLineItems);
            $this->addCustomerValueToChildLabel($customizedProductOptionLineItems, $customizedProductOptionValueLineItems);

            foreach ($productLineItems as $productLineItem) {
                $productLineItem->assign(['parentId' => null]);

                foreach ($customizedProductOptionLineItems as $child) {
                    $child->setParentId($productLineItem->getId());
                }

                $productLineItem->setChildren($customizedProductOptionLineItems);
                $productLineItem->setPosition($templateLineItem->getPosition());
            }
        }

        // removes all customized products entries except the product
        $orderLineItemCollection = $orderLineItemCollection->filter(static fn (OrderLineItemEntity $lineItem) => $lineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
            && $lineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
            && $lineItem->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        $orderLineItemCollection->sortByPosition();

        // reinsert options after its parent product
        $order->setLineItems($this->flattenOrderLineItemCollection($orderLineItemCollection));
    }

    private function isRelevantMailTemplate(string $templateId, Context $context): bool
    {
        $criteria = new Criteria([$templateId]);
        $criteria->addFilter(
            new EqualsAnyFilter('mailTemplateType.technicalName', [
                MailTemplateTypes::MAILTYPE_ORDER_CONFIRM,
                MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_TRANSACTION_STATE_PAID,
                MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_TRANSACTION_STATE_CANCELLED,
            ])
        );

        return $this->mailTemplateRepository->searchIds($criteria, $context)->firstId() !== null;
    }

    private function adjustQuantityAndRemoveProductNumber(
        OrderLineItemCollection $orderLineItemCollection
    ): void {
        foreach ($orderLineItemCollection as $lineItem) {
            $payload = $lineItem->getPayload();
            $isOneTimeSurcharge = \is_array($payload) && isset($payload['isOneTimeSurcharge']) && $payload['isOneTimeSurcharge'] === true;

            if ($lineItem->getPriceDefinition() instanceof PercentagePriceDefinition || $isOneTimeSurcharge) {
                $lineItem->setQuantity(1);
            }

            if (isset($payload['productNumber']) && $payload['productNumber'] === '*') {
                unset($payload['productNumber']);
                $lineItem->setPayload($payload);
            }
        }
    }

    private function addCustomerValueToChildLabel(OrderLineItemCollection $optionCollection, OrderLineItemCollection $optionValueCollection): void
    {
        foreach ($optionCollection as $option) {
            $option->setLabel(\sprintf('* %s', $option->getLabel()));

            // ToDo CUS-17 - Remove when nested line items are shown and loaded correctly
            /** @var OrderLineItemCollection $childLineItems */
            $childLineItems = $optionValueCollection->filterByProperty('parentId', $option->getId());
            if ($childLineItems->count() > 0) {
                foreach ($childLineItems as $childLineItem) {
                    $childLineItem->setLabel(\sprintf('* * %s', $childLineItem->getLabel()));
                }

                $option->setChildren($childLineItems);

                continue;
            }

            if ($value = $this->extractValueFromPayload($option)) {
                $option->setLabel(\sprintf('%s: %s', $option->getLabel(), $value));
            }
        }
    }

    private function extractValueFromPayload(OrderLineItemEntity $option): ?string
    {
        $payload = $option->getPayload() ?? [];
        $type = $payload['type'] ?? null;
        $value = $payload['value'] ?? '';

        $timeValue = (int) \strtotime($value);

        if (!$type || $type === Checkbox::NAME) {
            return null;
        }

        if ($type === DateTime::NAME) {
            $time = $value ? $timeValue : null;

            return \date('d.m.Y', $time);
        }

        if ($type === Timestamp::NAME) {
            $time = $value ? $timeValue : null;

            return \date('H:i', $time);
        }

        if ($type === ImageUpload::NAME || $type === FileUpload::NAME) {
            return \implode(', ', \array_column($option->getPayload()['media'] ?? [], 'filename'));
        }

        if (\strlen($value) > 50) {
            return \substr($value, 0, 45) . '[...]';
        }

        return $value;
    }

    private function flattenOrderLineItemCollection(OrderLineItemCollection $orderLineItemCollection): OrderLineItemCollection
    {
        $newOrderLineItemCollection = new OrderLineItemCollection();

        foreach ($orderLineItemCollection->getElements() as $item) {
            $newOrderLineItemCollection->add($item);
            $children = $item->getChildren();
            if ($children !== null) {
                $newOrderLineItemCollection->merge($this->flattenOrderLineItemCollection($children));
            }
        }

        return $newOrderLineItemCollection;
    }
}

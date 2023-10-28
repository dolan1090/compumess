<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\PayPal;

use Psr\Log\LoggerInterface;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Framework\Struct\Collection;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\PayPal\OrdersApi\Builder\Event\PayPalV2ItemFromCartEvent;
use Swag\PayPal\OrdersApi\Builder\Event\PayPalV2ItemFromOrderEvent;
use Swag\PayPal\PaymentsApi\Builder\Event\PayPalV1ItemFromCartEvent;
use Swag\PayPal\PaymentsApi\Builder\Event\PayPalV1ItemFromOrderEvent;
use Swag\PayPal\RestApi\V1\Api\Payment\Transaction\ItemList\Item as ItemV1;
use Swag\PayPal\RestApi\V2\Api\Order\PurchaseUnit\Item as ItemV2;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Translation\TranslatorInterface;

class PayPalLineItemSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PayPalV1ItemFromOrderEvent::class => 'adjustPayPalItemFromOrder',
            PayPalV1ItemFromCartEvent::class => 'adjustPayPalItemFromCart',
            PayPalV2ItemFromOrderEvent::class => 'adjustPayPalItemFromOrder',
            PayPalV2ItemFromCartEvent::class => 'adjustPayPalItemFromCart',
        ];
    }

    /**
     * @param PayPalV1ItemFromOrderEvent|PayPalV2ItemFromOrderEvent $event
     */
    public function adjustPayPalItemFromOrder(Event $event): void
    {
        $customProductsContainer = $event->getOriginalShopwareLineItem();
        if ($customProductsContainer->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE) {
            return;
        }

        $childLineItems = $customProductsContainer->getChildren();
        if ($childLineItems === null) {
            return;
        }

        $paypalLineItem = $event->getPayPalLineItem();
        $changedValues = $this->processCustomProductLineItems($childLineItems);

        $this->setName($paypalLineItem, $changedValues['name'], $customProductsContainer);
        $this->setSku($paypalLineItem, $changedValues['sku'], $customProductsContainer);
    }

    /**
     * @param PayPalV1ItemFromCartEvent|PayPalV2ItemFromCartEvent $event
     */
    public function adjustPayPalItemFromCart(Event $event): void
    {
        $customProductsContainer = $event->getOriginalShopwareLineItem();
        if ($customProductsContainer->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE) {
            return;
        }

        $paypalLineItem = $event->getPayPalLineItem();
        $changedValues = $this->processCustomProductLineItems($customProductsContainer->getChildren());

        $this->setName($paypalLineItem, $changedValues['name'], $customProductsContainer);
        $this->setSku($paypalLineItem, $changedValues['sku'], $customProductsContainer);
    }

    /**
     * @return array{'name': string, 'sku': string|null}
     */
    private function processCustomProductLineItems(OrderLineItemCollection|LineItemCollection $customProductLineItems): array
    {
        $name = '';
        $optionNames = [];
        $sku = null;
        $optionNumbers = [];
        foreach ($customProductLineItems as $customProductLineItem) {
            $label = (string) $customProductLineItem->getLabel();

            $productNumber = null;
            $payload = $customProductLineItem->getPayload();
            if ($payload !== null) {
                $productNumber = $payload['productNumber'] ?? null;
                if ($productNumber !== null) {
                    $productNumber = (string) $productNumber;
                }
            }

            if ($customProductLineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $name = $label;
                $sku = $productNumber;

                continue;
            }

            if ($customProductLineItem->getType() === CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE) {
                $optionValues = $customProductLineItem->getChildren();
                $this->processOptionLineItems($label, $productNumber, $optionNames, $optionNumbers, $optionValues);
            }
        }

        if ($optionNames !== []) {
            $hint = $this->translator->trans('customizedProducts.paypal.hint');
            $name .= \sprintf(' (%s: %s)', $hint, \implode(', ', $optionNames));
        }

        if ($sku !== null && $optionNumbers !== []) {
            $hint = $this->translator->trans('customizedProducts.paypal.hint');
            $sku .= \sprintf(' (%s: %s)', $hint, \implode(', ', $optionNumbers));
        }

        return ['name' => $name, 'sku' => $sku];
    }

    /**
     * @param OrderLineItemCollection|LineItemCollection|null $optionValues
     */
    private function processOptionLineItems(
        string $label,
        ?string $productNumber,
        array &$optionNames,
        array &$optionNumbers,
        ?Collection $optionValues
    ): void {
        if ($optionValues !== null && $optionValues->count() > 0) {
            $optionValueResult = $this->processOptionValueLineItems($optionValues, $label);
            $label = $optionValueResult['label'];
            $productNumber = $optionValueResult['productNumber'];
        }

        if ($label !== '') {
            $optionNames[] = $label;
        }

        if ($productNumber !== null && $productNumber !== CustomizedProductsCartDataCollector::FALLBACK_PRODUCT_NUMBER) {
            $optionNumbers[] = $productNumber;
        }
    }

    /**
     * @return array{'label': string, 'productNumber': string|null}
     */
    private function processOptionValueLineItems(OrderLineItemCollection|LineItemCollection $optionValues, string $label): array
    {
        $optionValueLabels = [];
        $optionValueNumbers = [];
        foreach ($optionValues as $optionValue) {
            if ($optionValue->getType() !== CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE) {
                continue;
            }

            $optionValueLabels[] = $optionValue->getLabel();
            $payload = $optionValue->getPayload();
            if ($payload !== null) {
                $optionValueNumbers[] = $payload['productNumber'] ?? null;
            }
        }

        if ($optionValueLabels !== []) {
            $label .= \sprintf(' (%s)', \implode(', ', $optionValueLabels));
        }

        $productNumber = null;
        if ($optionValueNumbers !== []) {
            $productNumber = \implode(', ', $optionValueNumbers);
        }

        return ['label' => $label, 'productNumber' => $productNumber];
    }

    private function setName(
        ItemV2|ItemV1 $item,
        string $name,
        OrderLineItemEntity|LineItem $customProductsContainer
    ): void {
        try {
            $item->setName($name);
        } catch (\LengthException $e) {
            $this->logger->warning($e->getMessage(), ['lineItem' => $customProductsContainer]);
            $item->setName(\substr($name, 0, $item::MAX_LENGTH_NAME));
        }
    }

    private function setSku(
        ItemV2|ItemV1 $item,
        ?string $sku,
        OrderLineItemEntity|LineItem $customProductsContainer
    ): void {
        if ($sku === null) {
            $item->setSku(null);

            return;
        }

        try {
            $item->setSku($sku);
        } catch (\LengthException $e) {
            $this->logger->warning($e->getMessage(), ['lineItem' => $customProductsContainer]);
            $item->setSku(\substr($sku, 0, $item::MAX_LENGTH_SKU));
        }
    }
}

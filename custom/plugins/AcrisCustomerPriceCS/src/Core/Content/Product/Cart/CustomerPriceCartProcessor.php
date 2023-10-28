<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Core\Content\Product\Cart;

use Acris\CustomerPrice\Components\CustomerPrice\CustomerPriceService;
use Acris\CustomerPrice\Components\Struct\LineItemCustomerPriceStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CustomerPriceCartProcessor implements CartDataCollectorInterface
{
    public function collect(
        CartDataCollection $data,
        Cart $original,
        SalesChannelContext $context,
        CartBehavior $behavior
    ): void {
        $lineItems = $original
            ->getLineItems()
            ->filterFlatByType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        foreach ($lineItems as $lineItem) {

            /** @var SalesChannelProductEntity $product */
            $product = $data->get('product-' . $lineItem->getReferencedId());

            if( ! $product instanceof SalesChannelProductEntity )
                continue;

            try {
                $this->addCustomerPricePayload( $lineItem, $product );
            }
            catch (\Exception $exception ) { }
        }
    }

    private function addCustomerPricePayload( LineItem  $lineItem, SalesChannelProductEntity $product )
    {

        /** @var LineItemCustomerPriceStruct $lineItemCustomerPriceStruct */
        $lineItemCustomerPriceStruct = $product->getExtension(CustomerPriceService::ACRIS_CUSTOMER_PRICE_LINE_ITEM_DISCOUNT);

        if( ! $lineItemCustomerPriceStruct )
            return;

        $payloadValue = [];
        $payloadValue['originalUnitPrice'] = $lineItemCustomerPriceStruct->getOriginalUnitPrice();

        $lineItem->setPayloadValue('acrisCustomerPrice', $payloadValue );
    }
}

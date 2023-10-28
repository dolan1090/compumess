<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class OrderReturnLineItemAllowedTypes
{
    public const LINE_ITEM_TYPES = [
        LineItem::PRODUCT_LINE_ITEM_TYPE,
        LineItem::CUSTOM_LINE_ITEM_TYPE,
        LineItem::CONTAINER_LINE_ITEM,
    ];
}

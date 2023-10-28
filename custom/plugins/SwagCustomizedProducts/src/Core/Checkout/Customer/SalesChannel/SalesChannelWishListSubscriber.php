<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Customer\SalesChannel;

use Shopware\Storefront\Page\Wishlist\WishListPageProductCriteriaEvent;
use Shopware\Storefront\Pagelet\Wishlist\GuestWishListPageletProductCriteriaEvent;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelWishListSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GuestWishListPageletProductCriteriaEvent::class => 'addCustomizedProductsToWishList',
            WishListPageProductCriteriaEvent::class => 'addCustomizedProductsToWishList',
        ];
    }

    public function addCustomizedProductsToWishList(GuestWishListPageletProductCriteriaEvent|WishListPageProductCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        $criteria->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }
}

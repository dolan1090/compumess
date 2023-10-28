<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Content\Product\SalesChannel;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelProductSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingCriteriaEvent::class => 'addCustomizedProductsListingAssociation',
        ];
    }

    public function addCustomizedProductsListingAssociation(ProductListingCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        $criteria->addAssociation('swagCustomizedProductsTemplate.options');
    }
}

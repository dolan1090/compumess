<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Content\Product\SalesChannel;

use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelProductRepositorySubscriber implements EventSubscriberInterface
{
    public function search(SalesChannelProcessCriteriaEvent $event): void
    {
        $event
            ->getCriteria()
            ->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.process.criteria' => 'search',
        ];
    }
}

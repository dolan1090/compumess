<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Subscriber;

use Shopware\Commercial\CustomPricing\Domain\CustomPriceExistenceHelper;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Category\Event\CategoryRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductDetailRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductListingRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestRouteCacheKeyEvent;
use Shopware\Core\Framework\Adapter\Cache\StoreApiRouteCacheKeyEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class CacheKeyEventSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly CustomPriceExistenceHelper $customPrice)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CategoryRouteCacheKeyEvent::class => ['disableCache', 100],
            ProductDetailRouteCacheKeyEvent::class => ['disableCache', 100],
            ProductSuggestRouteCacheKeyEvent::class => ['disableCache', 100],
            ProductListingRouteCacheKeyEvent::class => ['disableCache', 100],
        ];
    }

    public function disableCache(StoreApiRouteCacheKeyEvent $event): void
    {
        if (!License::get('CUSTOM_PRICES-2356553')) {
            return;
        }

        if (($customer = $event->getContext()->getCustomer()) !== null && $this->customPrice->existsForCustomPrice($customer)) {
            $event->disableCaching();
        }
    }
}

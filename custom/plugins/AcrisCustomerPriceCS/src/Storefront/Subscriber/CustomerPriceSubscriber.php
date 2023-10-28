<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Storefront\Subscriber;

use Acris\CustomerPrice\Custom\CustomerPriceEntity;
use Shopware\Core\Content\Category\Event\CategoryRouteCacheKeyEvent;
use Shopware\Core\Content\LandingPage\Event\LandingPageRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\CrossSellingRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductDetailRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductListingRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductSearchRouteCacheKeyEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestRouteCacheKeyEvent;
use Shopware\Core\Content\Product\SalesChannel\Detail\CachedProductDetailRoute;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Adapter\Cache\StoreApiRouteCacheKeyEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerPriceSubscriber implements EventSubscriberInterface
{
    const CUSTOMER_PRICE_WRITTEN_EVENT = 'acris_customer_price.written';

    public function __construct(private readonly CacheInvalidator $logger, private readonly EntityRepository $customerPriceRepository, private readonly EntityRepository $productRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            self::CUSTOMER_PRICE_WRITTEN_EVENT => 'onCustomerPriceWritten',
            ProductDetailRouteCacheKeyEvent::class => 'onProductRouteCacheKey',
            ProductListingRouteCacheKeyEvent::class => 'onProductRouteCacheKey',
            ProductSuggestRouteCacheKeyEvent::class => 'onProductRouteCacheKey',
            ProductSearchRouteCacheKeyEvent::class => 'onProductRouteCacheKey',
            CrossSellingRouteCacheKeyEvent::class => 'onProductRouteCacheKey',
            CategoryRouteCacheKeyEvent::class => 'onProductRouteCacheKey',
            LandingPageRouteCacheKeyEvent::class => 'onProductRouteCacheKey'
        ];
    }

    public function onCustomerPriceWritten(EntityWrittenEvent $event)
    {
        $results = $event->getWriteResults();
        foreach ($results as $result) {
            $payload = $result->getPayload();
            $productIds = [];

            if (!empty($payload) && array_key_exists('id', $payload) && !empty($payload['id'])) {
                $customerPriceId = $payload['id'];
                /** @var CustomerPriceEntity $customerPrice */
                $customerPrice = $this->customerPriceRepository->search((new Criteria([$customerPriceId])), $event->getContext())->first();
                if (empty($customerPrice) || empty($customerPrice->getProductId())) continue;

                $productResults = $this->productRepository->searchIds((new Criteria([$customerPrice->getProductId()])), $event->getContext());
                if ($productResults->getTotal() > 0 && $productResults->firstId()) $productIds[] = $productResults->firstId();

                if (!empty($productIds)) {
                    $this->logger->invalidate(
                        array_map([CachedProductDetailRoute::class, 'buildName'], $productIds)
                    );
                }
            }
        }
    }

    public function onProductRouteCacheKey(StoreApiRouteCacheKeyEvent $event): void
    {
        if (empty($event->getContext()) || empty($event->getContext()->getCustomer())) return;

        $event->addPart($event->getContext()->getCustomer()->getId());
    }
}

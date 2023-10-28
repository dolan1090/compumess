<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class ProductPageSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageCriteriaEvent::class => ['enrichCriteria', 100],
        ];
    }

    public function enrichCriteria(ProductPageCriteriaEvent $event): void
    {
        if (!License::get('REVIEW_SUMMARY-8147095')) {
            return;
        }

        $event->getCriteria()->addAssociation('reviewSummaries');
    }
}

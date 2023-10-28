<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Core\Content\Category\CategoryRule;

use Shopware\Core\Content\Category\SalesChannel\CachedNavigationRoute;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use Shopware\Core\Framework\Log\Package;
use Swag\DynamicAccess\DataAbstractionLayer\CategoryRule\CategoryRuleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

#[Package('content')]
class CategoryRuleSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInvalidator $cacheInvalidator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CategoryRuleEvents::CATEGORY_RULE_DELETED_EVENT => 'invalidateNavigationRoute',
        ];
    }

    public function invalidateNavigationRoute(EntityDeletedEvent $event): void
    {
        $payloads = $event->getPayloads();
        if (empty($payloads)) {
            return;
        }

        $ids = \array_column(\array_filter($payloads, function(array $payload) {
            return isset($payload['categoryId']);
        }), 'categoryId');

        if (empty($ids)) {
            return;
        }

        $logs = \array_map([CachedNavigationRoute::class, 'buildName'], $ids);
        $logs[] = CachedNavigationRoute::BASE_NAVIGATION_TAG;

        $this->cacheInvalidator->invalidate($logs);
    }
}

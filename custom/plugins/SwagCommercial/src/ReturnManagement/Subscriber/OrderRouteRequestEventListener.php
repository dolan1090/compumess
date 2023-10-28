<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Event\RouteRequest\OrderRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class OrderRouteRequestEventListener implements EventSubscriberInterface
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    public static function getSubscribedEvents(): array
    {
        return [
            OrderRouteRequestEvent::class => 'onOrderRouteRequest',
        ];
    }

    public function onOrderRouteRequest(OrderRouteRequestEvent $event): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociations(['returns', 'lineItems.state', 'lineItems.returns']);
    }
}

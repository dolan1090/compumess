<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Checkout;

use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Event\RouteRequest\OrderRouteRequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class OrderRouteRequestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderRouteRequestEvent::class => 'onOrderRouteRequest',
        ];
    }

    public function onOrderRouteRequest(OrderRouteRequestEvent $event): void
    {
        $event->getCriteria()->addAssociation('orderEmployee');
    }
}

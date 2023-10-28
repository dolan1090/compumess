<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Flow;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery\EmployeeAccountRecoverRequestEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeAccountInviteEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeAccountStatusChangedEvent;
use Shopware\Core\Framework\Event\BusinessEventCollector;
use Shopware\Core\Framework\Event\BusinessEventCollectorEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class BusinessEventCollectorSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly BusinessEventCollector $businessEventCollector)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BusinessEventCollectorEvent::NAME => ['onAddEvent', 1000],
        ];
    }

    public function onAddEvent(BusinessEventCollectorEvent $event): void
    {
        $collection = $event->getCollection();

        $definition = $this->businessEventCollector->define(EmployeeAccountRecoverRequestEvent::class);
        if ($definition) {
            $collection->set($definition->getName(), $definition);
        }

        $definition = $this->businessEventCollector->define(EmployeeAccountInviteEvent::class);
        if ($definition) {
            $collection->set($definition->getName(), $definition);
        }

        $definition = $this->businessEventCollector->define(EmployeeAccountStatusChangedEvent::class);
        if ($definition) {
            $collection->set($definition->getName(), $definition);
        }
    }
}

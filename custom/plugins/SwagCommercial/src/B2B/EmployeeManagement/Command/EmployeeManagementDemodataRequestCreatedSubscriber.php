<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Command;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Demodata\Event\DemodataRequestCreatedEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class EmployeeManagementDemodataRequestCreatedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            DemodataRequestCreatedEvent::class => 'onDemodataRequestCreated',
        ];
    }

    public function onDemodataRequestCreated(DemodataRequestCreatedEvent $event): void
    {
        if (!License::get('EMPLOYEE_MANAGEMENT-1264745')) {
            return;
        }

        $request = $event->getRequest();
        $input = $event->getInput();

        $count = $input->getOption('employee-management-data');
        if (\is_string($count) && (int) $count > 0) {
            $request->add(EmployeeDefinition::class, (int) $count);
        }
    }
}

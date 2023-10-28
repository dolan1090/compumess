<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class PermissionCollectorEvent extends Event
{
    public function __construct(private readonly PermissionEventCollection $events)
    {
    }

    public function getCollection(): PermissionEventCollection
    {
        return $this->events;
    }
}

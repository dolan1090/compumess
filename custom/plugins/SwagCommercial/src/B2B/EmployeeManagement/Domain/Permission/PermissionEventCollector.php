<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Permission\PermissionCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class PermissionEventCollector
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $permissionRepository,
    ) {
    }

    public function collect(Context $context): PermissionEventCollection
    {
        $event = new PermissionCollectorEvent(BaseEmployeePermissions::getBaseCollection());

        /** @var PermissionCollection $appPermissions */
        $appPermissions = $this->permissionRepository->search(new Criteria(), $context)->getEntities();
        $permissions = $event->getCollection();

        foreach ($appPermissions->getPermissionEventCollection() as $entity) {
            $permissions->add($entity);
        }

        $this->eventDispatcher->dispatch($event);

        return $permissions;
    }
}

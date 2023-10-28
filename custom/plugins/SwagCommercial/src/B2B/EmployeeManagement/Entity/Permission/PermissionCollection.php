<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Permission;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEvent;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<PermissionEntity>
 */
#[Package('checkout')]
class PermissionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'permission_collection';
    }

    public function getPermissionEventCollection(): PermissionEventCollection
    {
        $permissionEventCollection = new PermissionEventCollection();

        /** @var PermissionEntity $element */
        foreach ($this->getElements() as $element) {
            $permissionEventCollection->add(new PermissionEvent(
                $element->getName(),
                $element->getDependencies(),
                $element->getGroup()
            ));
        }

        return $permissionEventCollection;
    }

    protected function getExpectedClass(): string
    {
        return PermissionEntity::class;
    }
}

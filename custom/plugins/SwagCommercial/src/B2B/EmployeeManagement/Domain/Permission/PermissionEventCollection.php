<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Collection;

/**
 * @extends Collection<PermissionEvent>
 */
#[Package('checkout')]
class PermissionEventCollection extends Collection
{
    /**
     * @param string[] $dependencies
     */
    public function addPermission(string $name, string $group, array $dependencies = []): void
    {
        $this->add(new PermissionEvent($name, $dependencies, $group));
    }

    /**
     * @return array<string, array<int, PermissionEvent>>
     */
    public function getGroupedPermissions(): array
    {
        $groups = [];
        foreach ($this->getElements() as $element) {
            $groups[$element->getPermissionGroupName()][] = $element;
        }

        return $groups;
    }

    protected function getExpectedClass(): ?string
    {
        return PermissionEvent::class;
    }
}

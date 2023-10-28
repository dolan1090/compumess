<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Entity\Role;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<RoleEntity>
 */
#[Package('checkout')]
class RoleCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'role_collection';
    }

    protected function getExpectedClass(): string
    {
        return RoleEntity::class;
    }
}

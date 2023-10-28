<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Response;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Permission\PermissionEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class PermissionResponse extends StoreApiResponse
{
    /**
     * @var PermissionEntity
     */
    protected $object;

    public function __construct(PermissionEntity $object)
    {
        parent::__construct($object);
    }

    public function getPermission(): PermissionEntity
    {
        return $this->object;
    }
}

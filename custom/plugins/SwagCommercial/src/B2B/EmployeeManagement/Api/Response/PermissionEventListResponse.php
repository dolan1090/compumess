<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Response;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission\PermissionEventCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class PermissionEventListResponse extends StoreApiResponse
{
    /**
     * @var PermissionEventCollection
     */
    protected $object;

    public function __construct(PermissionEventCollection $object)
    {
        parent::__construct($object);
    }

    public function getPermissions(): PermissionEventCollection
    {
        return $this->object;
    }
}

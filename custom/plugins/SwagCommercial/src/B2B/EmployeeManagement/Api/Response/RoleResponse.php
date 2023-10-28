<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Response;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class RoleResponse extends StoreApiResponse
{
    /**
     * @var RoleEntity
     */
    protected $object;

    public function __construct(RoleEntity $object)
    {
        parent::__construct($object);
    }

    public function getRole(): RoleEntity
    {
        return $this->object;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Response;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class EmployeeResponse extends StoreApiResponse
{
    /**
     * @var EmployeeEntity
     */
    protected $object;

    public function __construct(EmployeeEntity $object)
    {
        parent::__construct($object);
    }

    public function getEmployee(): EmployeeEntity
    {
        return $this->object;
    }
}

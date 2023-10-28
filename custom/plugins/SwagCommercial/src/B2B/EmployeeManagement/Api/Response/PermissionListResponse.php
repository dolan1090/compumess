<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Response;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class PermissionListResponse extends StoreApiResponse
{
    /**
     * @var EntitySearchResult
     */
    protected $object;

    public function __construct(EntitySearchResult $object)
    {
        parent::__construct($object);
    }

    public function getPermissions(): EntitySearchResult
    {
        return $this->object;
    }
}

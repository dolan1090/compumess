<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class IsEmployeeRecoveryExpiredResponse extends StoreApiResponse
{
    /**
     * @var ArrayStruct<string, bool>
     */
    protected $object;

    public function __construct(bool $expired)
    {
        parent::__construct(new ArrayStruct(['isExpired' => $expired]));
    }

    public function isExpired(): bool
    {
        return (bool) $this->object->get('isExpired');
    }
}

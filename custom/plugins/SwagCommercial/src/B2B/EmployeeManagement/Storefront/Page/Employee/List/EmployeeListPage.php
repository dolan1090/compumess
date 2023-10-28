<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\Page\Employee\List;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Page\Page;

#[Package('checkout')]
class EmployeeListPage extends Page
{
    protected ?EntitySearchResult $employees = null;

    public function getEmployees(): ?EntitySearchResult
    {
        return $this->employees;
    }

    public function setEmployees(EntitySearchResult $employees): void
    {
        $this->employees = $employees;
    }
}

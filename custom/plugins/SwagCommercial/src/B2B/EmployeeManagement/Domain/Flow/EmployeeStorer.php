<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Flow;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Flag\EmployeeAware;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Content\Flow\Dispatching\Storer\FlowStorer;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class EmployeeStorer extends FlowStorer
{
    /**
     * @internal
     */
    public function __construct(private readonly EntityRepository $employeeRepository)
    {
    }

    /**
     * @param array<string, mixed> $stored
     *
     * @return array<string, mixed>
     */
    public function store(FlowEventAware $event, array $stored): array
    {
        if (!$event instanceof EmployeeAware || isset($stored[EmployeeAware::EMPLOYEE_ID])) {
            return $stored;
        }

        $stored[EmployeeAware::EMPLOYEE_ID] = $event->getEmployeeId();

        return $stored;
    }

    public function restore(StorableFlow $storable): void
    {
        if (!$storable->hasStore(EmployeeAware::EMPLOYEE_ID)) {
            return;
        }

        $storable->setData(EmployeeAware::EMPLOYEE_ID, $storable->getStore(EmployeeAware::EMPLOYEE_ID));

        $storable->lazy(
            EmployeeAware::EMPLOYEE,
            $this->lazyLoad(...)
        );
    }

    private function lazyLoad(StorableFlow $storableFlow): ?EmployeeEntity
    {
        $id = $storableFlow->getStore(EmployeeAware::EMPLOYEE_ID);

        if (!\is_string($id)) {
            return null;
        }

        $employee = $this->employeeRepository->search(new Criteria([$id]), $storableFlow->getContext())->get($id);

        if ($employee instanceof EmployeeEntity) {
            return $employee;
        }

        return null;
    }
}

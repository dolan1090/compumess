<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api'], '_contextTokenRequired' => true])]
#[Package('checkout')]
class EmployeeRecoveryIsExpiredRoute extends AbstractEmployeeRecoveryIsExpiredRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $employeeRepository,
    ) {
    }

    public function getDecorated(): AbstractEmployeeRecoveryIsExpiredRoute
    {
        throw EmployeeManagementException::decorationPattern(self::class);
    }

    #[Route(path: '/store-api/account/is-employee-recovery-expired', name: 'store-api.account.employee.recovery.is.expired', methods: ['POST'])]
    public function load(RequestDataBag $data, SalesChannelContext $context): IsEmployeeRecoveryExpiredResponse
    {
        $hash = $data->get('hash');

        if (!\is_string($hash)) {
            throw EmployeeManagementException::invalidRequestArgument('Parameter hash must be a string');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('recoveryHash', $hash));

        /** @var EmployeeEntity|null $employee */
        $employee = $this->employeeRepository->search($criteria, $context->getContext())->first();

        if (!$employee) {
            throw EmployeeManagementException::hashExpired($hash);
        }

        // recovery time is valid for 2 hours
        $validDateTime = (new \DateTime())->sub(new \DateInterval('PT2H'));

        return new IsEmployeeRecoveryExpiredResponse($employee->getRecoveryTime() < $validDateTime);
    }
}

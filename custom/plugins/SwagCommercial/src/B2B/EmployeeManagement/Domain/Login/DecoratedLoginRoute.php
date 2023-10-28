<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Login;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLoginRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Context\CartRestorer;
use Shopware\Core\System\SalesChannel\ContextTokenResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['store-api'], '_contextTokenRequired' => true])]
#[Package('checkout')]
class DecoratedLoginRoute extends AbstractLoginRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractLoginRoute $decorated,
        private readonly EntityRepository $employeeRepository,
        private readonly CartRestorer $restorer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getDecorated(): AbstractLoginRoute
    {
        return $this->decorated;
    }

    #[Route(path: '/store-api/account/login', name: 'store-api.account.login', methods: ['POST'])]
    public function login(RequestDataBag $data, SalesChannelContext $context): ContextTokenResponse
    {
        $email = $data->get('email', $data->get('username'));
        $password = $data->get('password');

        if (empty($email) || empty($password) || !\is_string($email) || !\is_string($password)) {
            return $this->decorated->login($data, $context);
        }

        $employee = $this->getCustomerByEmployeeLogin($email, $password, $context);

        if ($employee === null) {
            return $this->decorated->login($data, $context);
        }

        /** @var CustomerEntity $customer */
        $customer = $employee->getBusinessPartnerCustomer();

        $b2bToken = md5($customer->getId() . '_' . $employee->getId());
        $context = $this->restorer->restoreByToken($b2bToken, $customer->getId(), $context);
        $newToken = $context->getToken();

        $event = new CustomerLoginEvent($context, $customer, $newToken);
        $this->eventDispatcher->dispatch($event);

        $mainRequest = $this->requestStack->getMainRequest();

        if ($mainRequest !== null) {
            $mainRequest->getSession()->set('employeeId', $employee->getId());
        }

        return new ContextTokenResponse($newToken);
    }

    private function getCustomerByEmployeeLogin(string $email, string $password, SalesChannelContext $context): ?EmployeeEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('email', $email));
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('businessPartnerCustomer.boundSalesChannelId', $context->getSalesChannelId()),
            new EqualsFilter('businessPartnerCustomer.boundSalesChannelId', null),
        ]));
        $criteria->addAssociation('businessPartnerCustomer');

        /** @var EmployeeEntity|null $employee */
        $employee = $this->employeeRepository->search($criteria, $context->getContext())->first();

        if (!$employee || !$this->invalidBusinessPartner($employee, $context)) {
            return null;
        }

        if ($employee->getPassword() === null || !password_verify($password, $employee->getPassword())) {
            throw EmployeeManagementException::badCredentials();
        }

        return $employee;
    }

    private function invalidBusinessPartner(EmployeeEntity $employee, SalesChannelContext $context): bool
    {
        $businessPartner = $employee->getBusinessPartnerCustomer();

        if (!$businessPartner || $businessPartner->getGuest()) {
            return false;
        }

        $boundSalesChannelId = $businessPartner->getBoundSalesChannelId();

        return $employee->isActive() && $employee->getRecoveryHash() === null
            && ($boundSalesChannelId === $context->getSalesChannelId() || $boundSalesChannelId === null);
    }
}

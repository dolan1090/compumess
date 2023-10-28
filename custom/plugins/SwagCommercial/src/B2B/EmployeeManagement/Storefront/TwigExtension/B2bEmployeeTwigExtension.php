<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Storefront\TwigExtension;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

#[Package('checkout')]
class B2bEmployeeTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isB2bAdmin', $this->isB2bAdmin(...), ['needs_context' => true]),
            new TwigFunction('isB2bEmployee', $this->isB2bEmployee(...), ['needs_context' => true]),
            new TwigFunction('isB2bAllowed', $this->isB2bAllowed(...), ['needs_context' => true]),
        ];
    }

    /**
     * @param array<mixed> $context
     */
    public function isB2bAdmin(array $context): bool
    {
        if (!License::get('EMPLOYEE_MANAGEMENT-4838834')) {
            return false;
        }

        $customer = $this->getCustomer($context);

        if (!$customer) {
            return false;
        }

        return $customer->hasExtension('b2bBusinessPartner') && !$customer->hasExtension('b2bEmployee');
    }

    /**
     * @param array<mixed> $context
     */
    public function isB2bEmployee(array $context): bool
    {
        if (!License::get('EMPLOYEE_MANAGEMENT-4838834')) {
            return false;
        }

        $customer = $this->getCustomer($context);

        if (!$customer) {
            return false;
        }

        return $customer->hasExtension('b2bEmployee');
    }

    /**
     * @param array<mixed> $context
     */
    public function isB2bAllowed(array $context, string $permission): bool
    {
        $customer = $this->getCustomer($context);

        if (!$customer) {
            return false;
        }

        $employee = $customer->getExtension('b2bEmployee');

        if (!$employee instanceof EmployeeEntity || !$employee->getRole()) {
            return false;
        }

        return $employee->getRole()->can($permission);
    }

    /**
     * @param array<mixed> $context
     */
    private function getCustomer(array $context): ?CustomerEntity
    {
        /** @var SalesChannelContext $salesChannelContext */
        $salesChannelContext = $context['context'];

        return $salesChannelContext->getCustomer();
    }
}

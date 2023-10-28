<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Permission;

use Shopware\Commercial\B2B\EmployeeManagement\Api\Annotation\B2bEmployeePermissionValidator;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * @internal
 */
#[Package('checkout')]
class B2bBusinessPartnerResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if ($argument->getType() !== BusinessPartnerEntity::class) {
            return;
        }

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof SalesChannelContext) {
            return;
        }

        $customer = $context->getCustomer();

        if (!$customer) {
            throw EmployeeManagementException::businessPartnerNotLoggedIn();
        }

        $businessPartner = $customer->getExtension('b2bBusinessPartner');

        if (!$businessPartner instanceof BusinessPartnerEntity) {
            throw EmployeeManagementException::businessPartnerNotLoggedIn();
        }

        $permissions = $request->attributes->get(B2bEmployeePermissionValidator::ATTRIBUTE_B2B_EMPLOYEE_PERMISSIONS);
        $permissions = \is_array($permissions) ? $permissions : [];

        // if the user is an employee but no permissions are required, then only the business partner is allowed
        if (empty($permissions) && $customer->getExtension('b2bEmployee')) {
            throw EmployeeManagementException::businessPartnerNotLoggedIn();
        }

        yield $businessPartner;
    }
}

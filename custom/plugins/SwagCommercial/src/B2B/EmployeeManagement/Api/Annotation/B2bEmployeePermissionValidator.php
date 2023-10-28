<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Api\Annotation;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[Package('checkout')]
class B2bEmployeePermissionValidator implements EventSubscriberInterface
{
    public const ATTRIBUTE_B2B_EMPLOYEE_PERMISSIONS = '_b2bEmployeeCan';

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['validate', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE],
            ],
        ];
    }

    public function validate(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        $permissions = $request->attributes->get(self::ATTRIBUTE_B2B_EMPLOYEE_PERMISSIONS);
        /** @var string[] $permissions */
        $permissions = \is_array($permissions) ? $permissions : [];

        if (empty($permissions)) {
            return;
        }

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $customer = $context instanceof SalesChannelContext ? $context->getCustomer() : null;

        if (!$customer) {
            throw CartException::customerNotLoggedIn();
        }

        $employee = $customer->getExtension('b2bEmployee');

        if (!$employee instanceof EmployeeEntity) {
            return;
        }

        if (!$employee->getRole() || !$employee->getRole()->can(...$permissions)) {
            throw EmployeeManagementException::employeeMissingPermissions($permissions);
        }
    }
}

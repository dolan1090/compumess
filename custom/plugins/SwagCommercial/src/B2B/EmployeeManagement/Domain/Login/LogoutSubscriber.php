<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Login;

use Shopware\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[Package('checkout')]
class LogoutSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerLogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(): void
    {
        $mainRequest = $this->requestStack->getMainRequest();

        if (!$mainRequest?->hasSession()) {
            return;
        }

        $mainRequest->getSession()->remove('employeeId');
    }
}

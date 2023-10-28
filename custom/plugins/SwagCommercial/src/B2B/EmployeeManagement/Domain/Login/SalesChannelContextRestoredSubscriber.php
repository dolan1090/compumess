<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Login;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Customer\SalesChannel\AbstractLogoutRoute;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\Event\SalesChannelContextCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[Package('checkout')]
class SalesChannelContextRestoredSubscriber implements EventSubscriberInterface
{
    public const CUSTOMER_EMPLOYEE_EXTENSION = 'b2bEmployee';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $employeeRepository,
        private readonly RequestStack $requestStack,
        private readonly AbstractLogoutRoute $logoutRoute
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextCreatedEvent::class => 'onSalesChannelContextCreated',
        ];
    }

    public function onSalesChannelContextCreated(SalesChannelContextCreatedEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        $customer = $context->getCustomer();
        $mainRequest = $this->requestStack->getMainRequest();

        if (!$customer || $mainRequest === null || $mainRequest->hasSession() === false) {
            return;
        }

        $employeeId = $mainRequest->getSession()->get('employeeId');

        if (!\is_string($employeeId)) {
            return;
        }

        $criteria = new Criteria([$employeeId]);
        $criteria->addAssociation('role');
        $criteria->addFilter(new EqualsFilter('active', true));

        $employee = $this->employeeRepository->search($criteria, $event->getContext())->first();

        if (!$employee instanceof EmployeeEntity) {
            $this->logoutRoute->logout($context, new RequestDataBag());

            throw CartException::customerNotLoggedIn();
        }

        $customer->addExtension(self::CUSTOMER_EMPLOYEE_EXTENSION, $employee);
    }
}

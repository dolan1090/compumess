<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\RouteAccess;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Login\SalesChannelContextRestoredSubscriber;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

/**
 * @internal
 */
#[Package('checkout')]
class B2bRouteBlocker
{
    public function __construct(
        private readonly Connection $connection,
        private readonly AbstractEmployeeRouteAccessLoader $routeAccessLoader,
    ) {
    }

    public function blockRouteForEmployee(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $route = $request->get('_route');

        $denied = $this->routeAccessLoader->load()['denied'] ?? [];

        if (!\in_array($route, $denied, true)) {
            return;
        }

        $context = $request->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $customer = $context instanceof SalesChannelContext ? $context->getCustomer() : null;

        if (!$customer) {
            return;
        }

        $employee = $customer->getExtension(SalesChannelContextRestoredSubscriber::CUSTOMER_EMPLOYEE_EXTENSION);

        if (!$employee instanceof EmployeeEntity) {
            return;
        }

        /** @var string $token */
        $token = $request->headers->get(PlatformRequest::HEADER_CONTEXT_TOKEN);

        $this->requireBusinessPartner($token, $customer->getId());
    }

    private function requireBusinessPartner(string $token, string $customerId): void
    {
        $sql = <<<'SQL'
            SELECT `customer_id`
            FROM `sales_channel_api_context`
            WHERE `token` = :token
        SQL;

        $id = $this->connection->fetchOne($sql, [
            'token' => $token,
        ]);

        if ($id === Uuid::fromHexToBytes($customerId)) {
            return;
        }

        throw EmployeeManagementException::businessPartnerNotLoggedIn();
    }
}

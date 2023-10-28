<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

#[Package('checkout')]
class EmailValidationService
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public function validateEmployees(string $email, ?string $salesChannelId = null, ?string $employeeId = null): bool
    {
        $query = $this->connection->createQueryBuilder();

        $query
            ->select('b2b_employee.id')
            ->addSelect('customer.guest')
            ->addSelect('customer.bound_sales_channel_id')
            ->from('b2b_employee')
            ->innerJoin('b2b_employee', CustomerDefinition::ENTITY_NAME, 'customer', 'b2b_employee.business_partner_customer_id = customer.id')
            ->where($query->expr()->eq('b2b_employee.email', $query->createPositionalParameter($email)));

        if ($employeeId !== null) {
            $query->andWhere($query->expr()->neq('b2b_employee.id', $query->createPositionalParameter(Uuid::fromHexToBytes($employeeId))));
        }

        $result = $query
            ->executeQuery()
            ->fetchAllAssociative();

        $isValid = empty($result);
        $isBoundInSameSalesChannel = !$isValid && $salesChannelId !== null && $this->isBoundInSameSalesChannel($result, $salesChannelId);

        if (!$isValid && $salesChannelId === null) {
            foreach ($result as $item) {
                if (!$item['guest']) {
                    return false;
                }
            }
        }

        return $isValid || !$isBoundInSameSalesChannel;
    }

    public function validateCustomers(string $email, ?string $salesChannelId = null): bool
    {
        $query = $this->connection->createQueryBuilder();

        $result = $query
            ->select('customer.id')
            ->addSelect('customer.guest')
            ->addSelect('customer.bound_sales_channel_id')
            ->from('customer')
            ->where($query->expr()->eq('customer.email', $query->createPositionalParameter($email)))
            ->executeQuery()
            ->fetchAllAssociative();

        $isValid = empty($result);
        $isBoundInSameSalesChannel = !$isValid && $salesChannelId !== null && $this->isBoundInSameSalesChannel($result, $salesChannelId);

        if (!$isValid && $salesChannelId === null) {
            foreach ($result as $item) {
                if (!$item['guest']) {
                    return false;
                }
            }
        }

        return $isValid || !$isBoundInSameSalesChannel;
    }

    /**
     * @param array<array<string, mixed>> $findings
     */
    private function isBoundInSameSalesChannel(array $findings, string $salesChannelId): bool
    {
        foreach ($findings as $finding) {
            $boundSalesChannelId = $finding['bound_sales_channel_id'];
            $isGuestCustomer = $finding['guest'];
            $isBoundInSameSalesChannel = $boundSalesChannelId === null || (\is_string($boundSalesChannelId) && Uuid::fromBytesToHex($boundSalesChannelId) === $salesChannelId);

            if (!$isGuestCustomer && $isBoundInSameSalesChannel) {
                return true;
            }
        }

        return false;
    }
}

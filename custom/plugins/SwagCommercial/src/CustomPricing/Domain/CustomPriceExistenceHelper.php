<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Domain;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

#[Package('inventory')]
class CustomPriceExistenceHelper
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public function existsForCustomPrice(CustomerEntity $customer): bool
    {
        return (bool) $this->connection->createQueryBuilder()
            ->select('1')
            ->from(CustomPriceDefinition::ENTITY_NAME)
            ->where('customer_id = :customerId')
            ->orWhere('customer_group_id = :customerGroupId')
            ->setParameter('customerId', Uuid::fromHexToBytes($customer->getId()))
            ->setParameter('customerGroupId', Uuid::fromHexToBytes($customer->getGroupId()))
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }
}

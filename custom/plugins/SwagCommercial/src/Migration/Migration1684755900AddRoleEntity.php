<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1684755900AddRoleEntity extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1684755900;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(<<<'SQL'
            CREATE TABLE IF NOT EXISTS `b2b_components_role` (
                `id`                    BINARY(16) NOT NULL,
                `business_partner_customer_id`   BINARY(16) NULL,
                `name`                  VARCHAR(255) NOT NULL,
                `permissions`           JSON NULL,
                `custom_fields`         JSON NULL,
                `created_at`            DATETIME(3) NOT NULL,
                `updated_at`            DATETIME(3) NULL,
                PRIMARY KEY (`id`),
                CONSTRAINT `fk.b2b_components_role.business_partner_customer_id` FOREIGN KEY (`business_partner_customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL);

        if (!$this->columnExists($connection, EmployeeDefinition::ENTITY_NAME, 'role_id')) {
            $connection->executeStatement(<<<'SQL'
                ALTER TABLE `b2b_employee`
                ADD COLUMN `role_id` BINARY(16) NULL AFTER `business_partner_customer_id`,
                ADD CONSTRAINT `fk.b2b_employee.role_id` FOREIGN KEY (`role_id`)
                    REFERENCES `b2b_components_role` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
            SQL);
        }

        if (!$this->columnExists($connection, BusinessPartnerDefinition::ENTITY_NAME, 'default_role_id')) {
            $connection->executeStatement(<<<'SQL'
                ALTER TABLE `b2b_business_partner`
                ADD COLUMN `default_role_id` BINARY(16) NULL AFTER `customer_id`,
                ADD CONSTRAINT `fk.b2b_business_partner.default_role_id` FOREIGN KEY (`default_role_id`)
                    REFERENCES `b2b_components_role` (`id`)
                    ON DELETE SET NULL ON UPDATE CASCADE;
            SQL);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

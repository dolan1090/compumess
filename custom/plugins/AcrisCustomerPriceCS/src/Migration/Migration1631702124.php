<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1631702124 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1631702124;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_customer_price` (
                `id` BINARY(16) NOT NULL,
                `version_id` BINARY(16) NOT NULL,
                `active` TINYINT(1) NULL DEFAULT '0',
                `product_id` BINARY(16) NOT NULL,
                `product_version_id` BINARY(16) NOT NULL,
                `customer_id` BINARY(16) NOT NULL,
                `list_price_type` VARCHAR(255) NULL,
                `active_from` DATETIME(3) NULL,
                `active_until` DATETIME(3) NULL,
                `custom_fields` JSON NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`,`version_id`),
                CONSTRAINT `json.acris_customer_price.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
                KEY `fk.acris_customer_price.product_id` (`product_id`,`product_version_id`),
                KEY `fk.acris_customer_price.customer_id` (`customer_id`),
                CONSTRAINT `fk.acris_customer_price.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_customer_price.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_customer_advanced_price` (
                `id` BINARY(16) NOT NULL,
                `version_id` BINARY(16) NOT NULL,
                `customer_price_id` BINARY(16) NOT NULL,
                `acris_customer_price_version_id` BINARY(16) NOT NULL,
                `price` JSON NOT NULL,
                `quantity_start` INT(11) NOT NULL,
                `quantity_end` INT(11) NULL,
                `custom_fields` JSON NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`id`,`version_id`),
                CONSTRAINT `json.acris_customer_advanced_price.price` CHECK (JSON_VALID(`price`)),
                CONSTRAINT `json.acris_customer_advanced_price.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
                KEY `fk.acris_customer_advanced_price.customer_price_id` (`customer_price_id`,`acris_customer_price_version_id`),
                CONSTRAINT `fk.acris_customer_advanced_price.customer_price_id` FOREIGN KEY (`customer_price_id`,`acris_customer_price_version_id`) REFERENCES `acris_customer_price` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `acris_customer_price_rule` (
                `customer_price_id` BINARY(16) NOT NULL,
                `acris_customer_price_version_id` BINARY(16) NOT NULL,
                `rule_id` BINARY(16) NOT NULL,
                `created_at` DATETIME(3) NOT NULL,
                PRIMARY KEY (`customer_price_id`,`rule_id`),
                KEY `fk.acris_customer_price_rule.customer_price_id` (`customer_price_id`,`acris_customer_price_version_id`),
                KEY `fk.acris_customer_price_rule.rule_id` (`rule_id`),
                CONSTRAINT `fk.acris_customer_price_rule.customer_price_id` FOREIGN KEY (`customer_price_id`,`acris_customer_price_version_id`) REFERENCES `acris_customer_price` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT `fk.acris_customer_price_rule.rule_id` FOREIGN KEY (`rule_id`) REFERENCES `rule` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement($query);

        $this->updateInheritance($connection, 'product', 'acrisCustomerPrice');
        $this->updateInheritance($connection, 'customer', 'acrisCustomerPrice');
        $this->updateInheritance($connection, 'rule', 'acrisCustomerPrices');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}






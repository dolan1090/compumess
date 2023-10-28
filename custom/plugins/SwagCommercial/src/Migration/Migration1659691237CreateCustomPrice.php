<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
#[Package('inventory')]
class Migration1659691237CreateCustomPrice extends MigrationStep
{
    use InheritanceUpdaterTrait;

    final public const CUSTOM_PRICE_INHERITANCE_FIELD = 'customPrice';

    public function getCreationTimestamp(): int
    {
        return 1659691237;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<EOF
CREATE TABLE IF NOT EXISTS `custom_price` (
    `id` BINARY(16) NOT NULL,
    `product_id` BINARY(16) NOT NULL,
    `product_version_id` BINARY(16) NOT NULL,
    `customer_id` BINARY(16) NULL,
    `customer_group_id` BINARY(16) NULL,
    `price` JSON NOT NULL,
    `created_at` DATETIME(3) NOT NULL,
    `updated_at` DATETIME(3) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk.custom_price.product_id.customer_id` (`product_id`,`customer_id`),
    UNIQUE KEY `uk.custom_price.product_id.customer_group_id` (`product_id`,`customer_group_id`),
    CONSTRAINT `json.custom_price.price` CHECK (JSON_VALID(`price`)),
    KEY `fk.custom_price.product_id` (`product_id`,`product_version_id`),
    KEY `fk.custom_price.customer_id` (`customer_id`),
    KEY `fk.custom_price.customer_group_id` (`customer_group_id`),
    CONSTRAINT `fk.custom_price.product_id` FOREIGN KEY (`product_id`,`product_version_id`) REFERENCES `product` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.custom_price.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk.custom_price.customer_group_id` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOF;

        $connection->executeStatement($sql);

        $featureSetInheritanceColumn = $connection->fetchOne(
            \sprintf('SHOW COLUMNS FROM `%s` WHERE `Field` LIKE :column;', ProductDefinition::ENTITY_NAME),
            ['column' => self::CUSTOM_PRICE_INHERITANCE_FIELD]
        );
        if (empty($featureSetInheritanceColumn)) {
            $this->updateInheritance(
                $connection,
                ProductDefinition::ENTITY_NAME,
                self::CUSTOM_PRICE_INHERITANCE_FIELD
            );
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}

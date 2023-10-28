<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\Translations;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1658418674ReturnManagement_CreateReturnTables extends MigrationStep
{
    use ImportTranslationsTrait;

    public function getCreationTimestamp(): int
    {
        return 1658418674;
    }

    public function update(Connection $connection): void
    {
        $this->createOrderReturnTable($connection);
        $this->addMoreStateFieldForOrderLineItem($connection);
        $this->createNumberRanges($connection);
        $this->createOrderReturnLineItemReasonTable($connection);
        $this->createOrderReturnLineItemReasonTranslationTable($connection);
        $this->createOrderReturnLineItemTable($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    private function createOrderReturnTable(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `order_return` (
            `id` BINARY(16) NOT NULL,
            `version_id` BINARY(16) NOT NULL,
            `order_id` BINARY(16) NOT NULL,
            `order_version_id` BINARY(16) NOT NULL,
            `price` JSON NULL,
            `shipping_costs` JSON NULL,
            `state_id` BINARY(16) NOT NULL,
            `amount_total` DOUBLE NULL,
            `amount_net` DOUBLE NULL,
            `return_number` VARCHAR(255) NOT NULL,
            `requested_at` DATETIME(3) NOT NULL,
            `internal_comment` LONGTEXT NULL,
            `created_by_id` BINARY(16) NULL,
            `updated_by_id` BINARY(16) NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`,`version_id`),
            CONSTRAINT `json.order_return.price` CHECK (JSON_VALID(`price`)),
            KEY `fk.order_return.order_id` (`order_id`,`order_version_id`),
            KEY `fk.order_return.state_id` (`state_id`),
            KEY `fk.order_return.created_by_id` (`created_by_id`),
            KEY `fk.order_return.updated_by_id` (`updated_by_id`),
            CONSTRAINT `fk.order_return.order_id` FOREIGN KEY (`order_id`,`order_version_id`) REFERENCES `order` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.order_return.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.order_return.created_by_id` FOREIGN KEY (`created_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
            CONSTRAINT `fk.order_return.updated_by_id` FOREIGN KEY (`updated_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);
    }

    private function createOrderReturnLineItemTable(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `order_return_line_item` (
            `id` BINARY(16) NOT NULL,
            `version_id` BINARY(16) NOT NULL,
            `order_return_id` BINARY(16) NOT NULL,
            `order_return_version_id` BINARY(16) NOT NULL,
            `order_line_item_id` BINARY(16) NOT NULL,
            `order_line_item_version_id` BINARY(16) NOT NULL,
            `reason_id` BINARY(16) NOT NULL,
            `quantity` INT(11) NOT NULL,
            `price` JSON NOT NULL,
            `refund_amount` DOUBLE NULL,
            `restock_quantity` INT(11) NULL,
            `internal_comment` LONGTEXT NULL,
            `custom_fields` JSON NULL,
            `state_id` BINARY(16) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            PRIMARY KEY (`id`,`version_id`),
            CONSTRAINT `json.order_return_line_item.price` CHECK (JSON_VALID(`price`)),
            CONSTRAINT `json.order_return_line_item.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
            KEY `fk.order_return_line_item.reason_id` (`reason_id`),
            KEY `fk.order_return_line_item.state_id` (`state_id`),
            KEY `fk.order_return_line_item.order_return_id` (`order_return_id`,`order_return_version_id`),
            KEY `fk.order_return_line_item.order_line_item_id` (`order_line_item_id`,`order_line_item_version_id`),
            CONSTRAINT `fk.order_return_line_item.reason_id` FOREIGN KEY (`reason_id`) REFERENCES `order_return_line_item_reason` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk.order_return_line_item.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
            CONSTRAINT `fk.order_return_line_item.order_return_id` FOREIGN KEY (`order_return_id`,`order_return_version_id`) REFERENCES `order_return` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.order_return_line_item.order_line_item_id` FOREIGN KEY (`order_line_item_id`,`order_line_item_version_id`) REFERENCES `order_line_item` (`id`,`version_id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);
    }

    private function createOrderReturnLineItemReasonTable(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `order_return_line_item_reason` (
            `id` BINARY(16) NOT NULL,
            `reason_key` VARCHAR(255) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            UNIQUE(`reason_key`),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);
    }

    private function createOrderReturnLineItemReasonTranslationTable(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `order_return_line_item_reason_translation` (
            `content` VARCHAR(255) NOT NULL,
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
            `order_return_line_item_reason_id` BINARY(16) NOT NULL,
            `language_id` BINARY(16) NOT NULL,
            PRIMARY KEY (`order_return_line_item_reason_id`,`language_id`),
            KEY `fk.orli_reason_translation.order_return_line_item_reason_id` (`order_return_line_item_reason_id`),
            KEY `fk.orli_reason_translation.language_id` (`language_id`),
            CONSTRAINT `fk.orli_reason_translation.order_return_line_item_reason_id` FOREIGN KEY (`order_return_line_item_reason_id`) REFERENCES `order_return_line_item_reason` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
            CONSTRAINT `fk.orli_reason_translation.language_id` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
        $connection->executeStatement($query);
    }

    private function createNumberRanges(Connection $connection): void
    {
        $definitionNumberRangeTypes = [
            'order_return' => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameDe' => 'Retouren',
                'nameEn' => 'Order Return',
            ],
        ];

        $definitionNumberRanges = [
            'order_return' => [
                'id' => Uuid::randomHex(),
                'name' => 'Order Returns',
                'nameDe' => 'Retouren',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['order_return']['id'],
                'pattern' => '{n}',
                'start' => 10000,
            ],
        ];

        $numberRangeTypeMapping = $connection->fetchAllKeyValue(
            'SELECT `technical_name`, COUNT(`id`) FROM `number_range_type` WHERE `technical_name` IN (:technical_names) GROUP BY technical_name',
            [
                'technical_names' => array_keys($definitionNumberRangeTypes),
            ],
            [
                'technical_names' => ArrayParameterType::STRING,
            ]
        );

        foreach ($definitionNumberRangeTypes as $typeName => $numberRangeType) {
            if (isset($numberRangeTypeMapping[$typeName])) {
                continue;
            }

            $connection->insert(
                'number_range_type',
                [
                    'id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'global' => $numberRangeType['global'],
                    'technical_name' => $typeName,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $translation = new Translations(
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameDe'],
                ],
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameEn'],
                ]
            );

            $this->importTranslation('number_range_type_translation', $translation, $connection);
        }

        foreach ($definitionNumberRanges as $numberRange) {
            $connection->insert(
                'number_range',
                [
                    'id' => Uuid::fromHexToBytes($numberRange['id']),
                    'global' => $numberRange['global'],
                    'type_id' => Uuid::fromHexToBytes($numberRange['typeId']),
                    'pattern' => $numberRange['pattern'],
                    'start' => $numberRange['start'],
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $translation = new Translations(
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['nameDe'],
                ],
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['name'],
                ],
            );

            $this->importTranslation('number_range_translation', $translation, $connection);
        }
    }

    private function addMoreStateFieldForOrderLineItem(Connection $connection): void
    {
        $columns = array_column($connection->fetchAllAssociative('SHOW COLUMNS FROM `order_line_item` WHERE `Field` = \'state_id\''), 'Field');
        if (\in_array('state_id', $columns, true)) {
            return;
        }

        $query = <<<SQL
            ALTER TABLE `order_line_item`
            ADD COLUMN `state_id` BINARY(16) NULL,
            ADD CONSTRAINT `fk.order_line_item.state_id` FOREIGN KEY (`state_id`) REFERENCES `state_machine_state` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
SQL;
        $connection->executeStatement($query);
    }
}

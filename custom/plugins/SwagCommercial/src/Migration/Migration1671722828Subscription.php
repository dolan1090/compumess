<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStateActions;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\StateMachineMigration;
use Shopware\Core\Migration\Traits\StateMachineMigrationTrait;
use Shopware\Core\Migration\Traits\Translations;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1671722828Subscription extends MigrationStep
{
    use ImportTranslationsTrait;
    use InheritanceUpdaterTrait;
    use StateMachineMigrationTrait;

    private const NUMBER_RANGE_TYPE_NAME = 'subscription';

    public function getCreationTimestamp(): int
    {
        return 1671722828;
    }

    public function update(Connection $connection): void
    {
        $this->createSubscriptionTable($connection);
        $this->createSubscriptionAddressTable($connection);
        $this->createSubscriptionCustomerTable($connection);
        $this->createStateMachine($connection);
        $this->addNumberRange($connection);

        $this->addReferenceColumnToProduct($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createSubscriptionTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription` (
                `id`                         BINARY(16)      NOT NULL,
                `converted_order`            JSON            NULL,
                `subscription_number`        VARCHAR(64)     NOT NULL,
                `auto_increment`             BIGINT unsigned NOT NULL AUTO_INCREMENT,
                `next_schedule`              DATETIME(3)     NOT NULL,
                `sales_channel_id`           BINARY(16)      NOT NULL,
                `subscription_plan_id`       BINARY(16)      NULL,
                `subscription_plan_name`     VARCHAR(255)    NOT NULL,
                `subscription_interval_id`   BINARY(16)      NULL,
                `subscription_interval_name` VARCHAR(255)    NOT NULL,
                `date_interval`              VARCHAR(255)    NOT NULL,
                `cron_interval`              VARCHAR(255)    NOT NULL,
                `billing_address_id`         BINARY(16)      NOT NULL,
                `shipping_address_id`        BINARY(16)      NOT NULL,
                `payment_method_id`          BINARY(16)      NOT NULL,
                `currency_id`                BINARY(16)      NOT NULL,
                `language_id`                BINARY(16)      NOT NULL,
                `shipping_method_id`         BINARY(16)      NOT NULL,
                `state_id`                   BINARY(16)      NOT NULL,
                `item_rounding`              json            NOT NULL,
                `total_rounding`             json            NOT NULL,
                `custom_fields`              JSON            NULL,
                `created_at`                 DATETIME(3)     NOT NULL,
                `updated_at`                 DATETIME(3)     NULL,
                PRIMARY KEY (`id`),
                INDEX `idx.subscription_number` (`subscription_number`),
                INDEX `idx.state_index` (`state_id`),
                INDEX `idx.next_schedule` (`next_schedule`),
                UNIQUE `uniq.auto_increment` (`auto_increment`),
                CONSTRAINT `json.subscription.converted_order` CHECK (JSON_VALID(`converted_order`)),
                CONSTRAINT `json.subscription.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
                CONSTRAINT `json.subscription.item_rounding` CHECK (JSON_VALID(`item_rounding`)),
                CONSTRAINT `json.subscription.total_rounding` CHECK (JSON_VALID(`total_rounding`)),
                CONSTRAINT `fk.subscription.sales_channel_id` FOREIGN KEY (`sales_channel_id`)
                    REFERENCES `sales_channel` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.subscription_plan_id` FOREIGN KEY (`subscription_plan_id`)
                    REFERENCES `subscription_plan` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.payment_method_id` FOREIGN KEY (`payment_method_id`)
                    REFERENCES `payment_method` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.currency_id` FOREIGN KEY (`currency_id`)
                    REFERENCES `currency` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.language_id` FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.shipping_method_id` FOREIGN KEY (`shipping_method_id`)
                    REFERENCES `shipping_method` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.subscription_interval_id` FOREIGN KEY (`subscription_interval_id`)
                    REFERENCES `subscription_interval` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription.state_id` FOREIGN KEY (`state_id`)
                    REFERENCES `state_machine_state` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function createSubscriptionCustomerTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_customer` (
                `id`                        BINARY(16)      NOT NULL,
                `subscription_id`           BINARY(16)      NOT NULL,
                `customer_id`               BINARY(16)      NULL,
                `salutation_id`             BINARY(16)      NULL,
                `email`                     VARCHAR(254)    NOT NULL,
                `first_name`                VARCHAR(50)     NOT NULL,
                `last_name`                 VARCHAR(60)     NOT NULL,
                `company`                   VARCHAR(255)    NULL,
                `title`                     VARCHAR(100)    NULL,
                `customer_number`           VARCHAR(255)    NULL,
                `custom_fields`             JSON            NULL,
                `vat_ids`                   JSON            NULL,
                `remote_address`            VARCHAR(255)    NULL,
                `created_at`                DATETIME(3)     NOT NULL,
                `updated_at`                DATETIME(3)     NULL,
                PRIMARY KEY (`id`),
                INDEX `idx.customer_number` (`customer_number`),
                CONSTRAINT `json.subscription_customer.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
                CONSTRAINT `fk.subscription_customer.customer_id` FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_customer.salutation_id` FOREIGN KEY (`salutation_id`)
                    REFERENCES `salutation` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.subscription_customer.subscription_id` FOREIGN KEY (`subscription_id`)
                    REFERENCES `subscription` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function createSubscriptionAddressTable(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `subscription_address` (
              `id`                          BINARY(16)      NOT NULL,
              `country_id`                  BINARY(16)      NOT NULL,
              `country_state_id`            BINARY(16)      NULL,
              `salutation_id`               BINARY(16)      NULL,
              `subscription_id`             BINARY(16)      NOT NULL,
              `first_name`                  VARCHAR(50)     NOT NULL,
              `last_name`                   VARCHAR(60)     NOT NULL,
              `street`                      VARCHAR(255)    NOT NULL,
              `zipcode`                     VARCHAR(50)     NOT NULL,
              `city`                        VARCHAR(70)     NOT NULL,
              `company`                     VARCHAR(255)    NULL,
              `department`                  VARCHAR(35)     NULL,
              `title`                       VARCHAR(100)    NULL,
              `vat_id`                      VARCHAR(50)     NULL,
              `phone_number`                VARCHAR(40)     NULL,
              `additional_address_line1`    VARCHAR(255)    NULL,
              `additional_address_line2`    VARCHAR(255)    NULL,
              `custom_fields`               JSON            NULL,
              `created_at`                  DATETIME(3)     NOT NULL,
              `updated_at`                  DATETIME(3)     NULL,
              PRIMARY KEY (`id`),
              CONSTRAINT `json.subscription_address.custom_fields` CHECK (JSON_VALID(`custom_fields`)),
              CONSTRAINT `fk.subscription_address.subscription_id` FOREIGN KEY (`subscription_id`)
                REFERENCES `subscription` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
              CONSTRAINT `fk.subscription_address.country_id` FOREIGN KEY (`country_id`)
                REFERENCES `country` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
              CONSTRAINT `fk.subscription_address.country_state_id` FOREIGN KEY (`country_state_id`)
                REFERENCES `country_state` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
              CONSTRAINT `fk.subscription_address.salutation_id` FOREIGN KEY (`salutation_id`)
                REFERENCES `salutation` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    private function createStateMachine(Connection $connection): void
    {
        $stateMachine = new StateMachineMigration(
            SubscriptionStates::STATE_MACHINE,
            'Abonnementstatus',
            'Subscription state',
            [
                StateMachineMigration::state(SubscriptionStates::STATE_ACTIVE, 'Aktiv', 'Active'),
                StateMachineMigration::state(SubscriptionStates::STATE_PAUSED, 'Pausiert', 'Paused'),
                StateMachineMigration::state(SubscriptionStates::STATE_CANCELLED, 'Gekündigt', 'Cancelled'),
                StateMachineMigration::state(SubscriptionStates::STATE_FLAGGED_CANCELLED, 'Für Kündigung markiert', 'Marked for cancellation'),
                StateMachineMigration::state(SubscriptionStates::STATE_PAYMENT_FAILED, 'Zahlung fehlgeschlagen', 'Payment failed'),
            ],
            [
                // paused -> active
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_ACTIVATE,
                    SubscriptionStates::STATE_PAUSED,
                    SubscriptionStates::STATE_ACTIVE
                ),
                // active -> paused
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_PAUSE,
                    SubscriptionStates::STATE_ACTIVE,
                    SubscriptionStates::STATE_PAUSED
                ),
                // active -> cancelled
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_CANCEL,
                    SubscriptionStates::STATE_ACTIVE,
                    SubscriptionStates::STATE_CANCELLED,
                ),
                // paused -> cancelled
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_CANCEL,
                    SubscriptionStates::STATE_PAUSED,
                    SubscriptionStates::STATE_CANCELLED,
                ),
                // active -> flagged cancelled
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_FLAG_FOR_CANCELLATION,
                    SubscriptionStates::STATE_ACTIVE,
                    SubscriptionStates::STATE_FLAGGED_CANCELLED
                ),
                // flagged cancelled -> active
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_ACTIVATE,
                    SubscriptionStates::STATE_FLAGGED_CANCELLED,
                    SubscriptionStates::STATE_ACTIVE
                ),
                // flagged cancelled -> cancelled
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_CANCEL,
                    SubscriptionStates::STATE_FLAGGED_CANCELLED,
                    SubscriptionStates::STATE_CANCELLED,
                ),
                // payment failed -> active
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_FAIL_PAYMENT,
                    SubscriptionStates::STATE_ACTIVE,
                    SubscriptionStates::STATE_PAYMENT_FAILED
                ),
                // payment failed -> cancelled
                StateMachineMigration::transition(
                    SubscriptionStateActions::ACTION_ACTIVATE,
                    SubscriptionStates::STATE_PAYMENT_FAILED,
                    SubscriptionStates::STATE_ACTIVE
                ),
            ],
            SubscriptionStates::STATE_ACTIVE
        );

        $this->import($stateMachine, $connection);
    }

    private function addNumberRange(Connection $connection): void
    {
        $type = $connection->fetchFirstColumn(
            'SELECT id FROM number_range_type WHERE technical_name = :technicalName',
            ['technicalName' => self::NUMBER_RANGE_TYPE_NAME]
        );

        if ($type) {
            return;
        }

        $numberRangeId = Uuid::randomBytes();
        $numberRangeTypeId = Uuid::randomBytes();

        $connection->insert('number_range_type', [
            'id' => $numberRangeTypeId,
            'global' => 0,
            'technical_name' => self::NUMBER_RANGE_TYPE_NAME,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('number_range', [
            'id' => $numberRangeId,
            'type_id' => $numberRangeTypeId,
            'global' => 1,
            'pattern' => '{n}',
            'start' => 10000,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $numberRangeTranslations = new Translations(
            ['number_range_id' => $numberRangeId, 'name' => 'Abo-Nummern'],
            ['number_range_id' => $numberRangeId, 'name' => 'Subscription numbers']
        );

        $numberRangeTypeTranslations = new Translations(
            ['number_range_type_id' => $numberRangeTypeId, 'type_name' => 'Abo-Nummern'],
            ['number_range_type_id' => $numberRangeTypeId, 'type_name' => 'Subscription numbers']
        );

        $this->importTranslation('number_range_translation', $numberRangeTranslations, $connection);
        $this->importTranslation('number_range_type_translation', $numberRangeTypeTranslations, $connection);
    }

    private function addReferenceColumnToProduct(Connection $connection): void
    {
        $manager = $connection->createSchemaManager();
        $columns = $manager->listTableColumns(ProductDefinition::ENTITY_NAME);

        // schema manager returns columns lowercase
        if (\array_key_exists('subscriptionplans', $columns)) {
            return;
        }

        $this->updateInheritance($connection, ProductDefinition::ENTITY_NAME, 'subscriptionPlans');
    }
}

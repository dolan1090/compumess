<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1644554091 extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1644554091;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
            ALTER TABLE `acris_customer_price`
                ADD COLUMN `rule_ids` JSON NULL,
                ADD CONSTRAINT `json.acris_customer_price.rule_ids` CHECK (JSON_VALID(`rule_ids`));
SQL;

        $connection->executeStatement($query);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}






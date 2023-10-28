<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\LandingPage\LandingPageDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\InheritanceUpdaterTrait;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\DynamicAccess\DataAbstractionLayer\LandingPageRule\LandingPageRuleDefinition;

class Migration1624516627LandingPageRule extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1624516627;
    }

    public function update(Connection $connection): void
    {
        $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS `#table#` (
    `#ref1#_id`         BINARY(16)  NOT NULL,
    `#ref1#_version_id` BINARY(16)  NOT NULL DEFAULT '#live_version#',
    `#ref2#_id`         BINARY(16)  NOT NULL,
    PRIMARY KEY (`#ref1#_id`, `#ref1#_version_id`, `#ref2#_id`),
    CONSTRAINT `fk.swag_dynamic_access_landing_page_rule_#ref1#`
        FOREIGN KEY (`#ref1#_id`, `#ref1#_version_id`) REFERENCES `#ref1#` (`id`, `version_id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk.swag_dynamic_access_landing_page_rule_#ref2#`
        FOREIGN KEY (`#ref2#_id`) REFERENCES `#ref2#` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
)

ENGINE = InnoDB
DEFAULT CHARSET = utf8mb4
COLLATE = utf8mb4_unicode_ci;
SQL;

        $connection->executeStatement(\str_replace(
            ['#table#', '#ref1#', '#ref2#', '#live_version#'],
            [LandingPageRuleDefinition::ENTITY_NAME, LandingPageDefinition::ENTITY_NAME, RuleDefinition::ENTITY_NAME, Uuid::fromHexToBytes(Defaults::LIVE_VERSION)],
            $sql
        ));
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}

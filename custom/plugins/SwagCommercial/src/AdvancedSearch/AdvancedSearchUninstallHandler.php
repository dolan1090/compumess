<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('buyers-experience')]
class AdvancedSearchUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        $this->dropAdvancedSearchTables($container->get(Connection::class));
    }

    private function dropAdvancedSearchTables(Connection $connection): void
    {
        $connection->executeStatement(
            'DROP TABLE IF EXISTS
    `advanced_search_config_field`,
    `advanced_search_config`,
    `advanced_search_entity_stream`,
    `advanced_search_entity_stream_filter`,
    `advanced_search_boosting`;

DELETE FROM `migration` WHERE `class` like :modules_name;',
            [
                'modules_name' => '%SWAGAdvancedSearch%',
            ]
        );
    }
}

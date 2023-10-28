<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate\AdvancedSearchConfigFieldDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\DefaultAdvancedSearchConfig;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('buyers-experience')]
class Migration1680751315SWAGAdvancedSearch_AddAdvancedSearchConfigurationDefaults extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1680751315;
    }

    public function update(Connection $connection): void
    {
        $this->createSearchConfigDefaultData($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    private function createSearchConfigDefaultData(Connection $connection): void
    {
        $salesChannelIds = $connection->fetchFirstColumn('SELECT id FROM sales_channel');
        $configSalesChannelIds = $connection->fetchFirstColumn('SELECT sales_channel_id FROM advanced_search_config');

        $salesChannelIds = array_diff($salesChannelIds, $configSalesChannelIds);

        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        foreach ($salesChannelIds as $salesChannelId) {
            $searchConfigId = Uuid::randomBytes();
            $connection->insert(
                AdvancedSearchConfigDefinition::ENTITY_NAME,
                array_merge([
                    'id' => $searchConfigId,
                    'sales_channel_id' => $salesChannelId,
                    'created_at' => $createdAt,
                ], DefaultAdvancedSearchConfig::getConfig())
            );

            $productData = $this->getDefaultConfigFields($searchConfigId, ProductDefinition::ENTITY_NAME, $createdAt);
            $manufacturerData = $this->getDefaultConfigFields($searchConfigId, ProductManufacturerDefinition::ENTITY_NAME, $createdAt);
            $categoryData = $this->getDefaultConfigFields($searchConfigId, CategoryDefinition::ENTITY_NAME, $createdAt);

            $defaultSearchData = array_merge($productData, $manufacturerData, $categoryData);

            $queue = new MultiInsertQueryQueue($connection, 250);

            foreach ($defaultSearchData as $searchData) {
                $queue->addInsert(AdvancedSearchConfigFieldDefinition::ENTITY_NAME, $searchData);
            }

            $queue->execute();
        }
    }

    /**
     * @return array<array<string, string|int|float>>
     */
    private function getDefaultConfigFields(string $configId, string $entityName, string $createdAt): array
    {
        $configs = DefaultAdvancedSearchConfig::getConfigFields($entityName);

        foreach ($configs as $index => $config) {
            $config['id'] = Uuid::randomBytes();
            $config['config_id'] = $configId;
            $config['entity'] = $entityName;
            $config['created_at'] = $createdAt;
            $configs[$index] = $config;
        }

        return $configs;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate\AdvancedSearchConfigFieldDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\DefaultAdvancedSearchConfig;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class SalesChannelCreatedSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelEvents::SALES_CHANNEL_WRITTEN => 'onSalesChannelWritten',
        ];
    }

    public function onSalesChannelWritten(EntityWrittenEvent $event): void
    {
        $writeResults = array_filter(
            $event->getWriteResults(),
            fn ($writeResult) => $writeResult->getOperation() === EntityWriteResult::OPERATION_INSERT
        );

        /** @var array<string> $ids */
        $ids = array_values(
            array_map(fn ($writeResult) => $writeResult->getPrimaryKey(), $writeResults)
        );

        if (empty($ids)) {
            return;
        }

        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
        foreach ($ids as $id) {
            $searchConfigId = Uuid::randomBytes();
            $this->connection->insert(
                AdvancedSearchConfigDefinition::ENTITY_NAME,
                array_merge([
                    'id' => $searchConfigId,
                    'sales_channel_id' => Uuid::fromHexToBytes($id),
                    'created_at' => $createdAt,
                ], DefaultAdvancedSearchConfig::getConfig())
            );

            $productData = $this->getDefaultConfigFields($searchConfigId, ProductDefinition::ENTITY_NAME, $createdAt);
            $manufacturerData = $this->getDefaultConfigFields($searchConfigId, ProductManufacturerDefinition::ENTITY_NAME, $createdAt);
            $categoryData = $this->getDefaultConfigFields($searchConfigId, CategoryDefinition::ENTITY_NAME, $createdAt);

            $defaultSearchData = array_merge($productData, $manufacturerData, $categoryData);

            $queue = new MultiInsertQueryQueue($this->connection, 250);

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

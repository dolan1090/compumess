<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\Indexing\ElasticsearchIndexer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class ManufacturerUpdater implements EventSubscriberInterface
{
    public function __construct(
        private readonly ElasticsearchIndexer $indexer,
        private readonly EntityDefinition $definition
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductManufacturerDefinition::ENTITY_NAME . '.written' => 'update',
        ];
    }

    public function update(EntityWrittenEvent $event): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        $this->indexer->updateIds(
            $this->definition,
            $event->getIds()
        );
    }
}

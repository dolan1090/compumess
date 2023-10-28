<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Shopware\Elasticsearch\Product\Event\ElasticsearchProductCustomFieldsMappingEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class ProductCustomFieldsMappingEventSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ElasticsearchProductCustomFieldsMappingEvent::class => 'addMapping',
        ];
    }

    public function addMapping(ElasticsearchProductCustomFieldsMappingEvent $event): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        $customFieldCollection = $this->getCustomFields();

        foreach ($customFieldCollection as $name => $type) {
            if ($type === CustomFieldTypes::JSON || $type === CustomFieldTypes::PRICE) {
                $type = CustomFieldTypes::TEXT;
            }

            $event->setMapping((string) $name, $type);
        }
    }

    /**
     * @return array<int|string, 'bool'|'checkbox'|'colorpicker'|'date'|'datetime'|'entity'|'float'|'html'|'int'|'json'|'media'|'number'|'price'|'select'|'switch'|'text'>
     */
    private function getCustomFields(): array
    {
        /** @var array<int|string, 'bool'|'checkbox'|'colorpicker'|'date'|'datetime'|'entity'|'float'|'html'|'int'|'json'|'media'|'number'|'price'|'select'|'switch'|'text'> $customFields */
        $customFields = $this->connection->fetchAllKeyValue('SELECT name, type FROM custom_field INNER JOIN `custom_field_set_relation` ON `custom_field`.`set_id` = `custom_field_set_relation`.`set_id` WHERE entity_name = :entityName', [
            'entityName' => ProductDefinition::ENTITY_NAME,
        ]);

        return $customFields;
    }
}

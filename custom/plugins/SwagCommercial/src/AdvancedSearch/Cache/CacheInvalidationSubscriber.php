<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Cache;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate\AdvancedSearchConfigFieldDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\Boosting\BoostingDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\Aggregate\EntityStreamFilterDefinition;
use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\EntityStreamDefinition;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @final
 *
 * @internal
 */
#[Package('buyers-experience')]
class CacheInvalidationSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInvalidator $cacheInvalidator)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents()
    {
        return [
            AdvancedSearchConfigDefinition::ENTITY_NAME . '.written' => [
                ['invalidateSearch', 2002],
            ],
            AdvancedSearchConfigFieldDefinition::ENTITY_NAME . '.written' => [
                ['invalidateSearch', 2002],
            ],
            EntityStreamFilterDefinition::ENTITY_NAME . '.written' => [
                ['invalidateSearch', 2002],
            ],
            EntityStreamDefinition::ENTITY_NAME . '.written' => [
                ['invalidateSearch', 2002],
            ],
            BoostingDefinition::ENTITY_NAME . '.written' => [
                ['invalidateSearch', 2002],
            ],
        ];
    }

    // invalidates the search and suggest route each time a term changed
    public function invalidateSearch(): void
    {
        // invalidates the search and suggest route each time a product changed
        $this->cacheInvalidator->invalidate([
            'product-suggest-route',
            'product-search-route',
        ]);
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\CrossSearch;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Product\SearchFieldConfig;

/**
 * @codeCoverageIgnore
 */
#[Package('buyers-experience')]
class CrossSearchFieldCollection
{
    /**
     * @var array<SearchFieldConfig>
     */
    private array $searchConfigs;

    public function __construct(private readonly EntityDefinition $crossDefinition)
    {
        $this->searchConfigs = [];
    }

    public function getCrossDefinition(): EntityDefinition
    {
        return $this->crossDefinition;
    }

    /**
     * @return array<SearchFieldConfig>
     */
    public function getSearchConfigs(): array
    {
        return $this->searchConfigs;
    }

    public function add(SearchFieldConfig $searchConfig): void
    {
        $this->searchConfigs[] = $searchConfig;
    }
}

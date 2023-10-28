<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Search;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

/**
 * @codeCoverageIgnore
 */
#[Package('buyers-experience')]
class MultiSearchResult extends Struct
{
    /**
     * @var EntitySearchResult[]
     */
    protected array $searchResults = [];

    public function addSearch(EntitySearchResult $entityResult, string $entityName): void
    {
        $this->searchResults[$entityName] = $entityResult;
    }

    public function getResult(string $entityName): ?EntitySearchResult
    {
        return $this->searchResults[$entityName] ?? null;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Suggest;

use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('buyers-experience')]
class MultiSuggestResult extends Struct
{
    /**
     * @var EntitySearchResult[]
     */
    private array $suggestResults = [];

    public function addSuggest(EntitySearchResult $entityResult, string $entityName): void
    {
        $this->suggestResults[$entityName] = $entityResult;
    }

    public function getResult(string $entity): ?EntitySearchResult
    {
        return $this->suggestResults[$entity] ?? null;
    }
}

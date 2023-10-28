<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting;

use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class Boosting
{
    /**
     * @internal
     *
     * @param array<string, mixed> $filter
     */
    public function __construct(
        private readonly float $boost,
        private readonly array $filter
    ) {
    }

    public function getBoost(): float
    {
        return $this->boost;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFilter(): array
    {
        return $this->filter;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\EntityStream;

use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\Aggregate\EntityStreamFilterCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class EntityStreamEntity extends Entity
{
    use EntityIdTrait;

    protected ?EntityStreamFilterCollection $filters = null;

    protected string $type;

    /**
     * @var array<array<string, string|array<array<string, mixed>>>>|null
     */
    protected ?array $apiFilter = null;

    protected bool $invalid;

    /**
     * @return array<array<string, string|array<array<string, mixed>>>>|null
     */
    public function getApiFilter(): ?array
    {
        return $this->apiFilter;
    }

    /**
     * @param array<array<string, string|array<array<string, mixed>>>> $apiFilter
     */
    public function setApiFilter(array $apiFilter): void
    {
        $this->apiFilter = $apiFilter;
    }

    public function getFilters(): ?EntityStreamFilterCollection
    {
        return $this->filters;
    }

    public function setFilters(EntityStreamFilterCollection $filters): void
    {
        $this->filters = $filters;
    }

    public function isInvalid(): bool
    {
        return $this->invalid;
    }

    public function setInvalid(bool $invalid): void
    {
        $this->invalid = $invalid;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}

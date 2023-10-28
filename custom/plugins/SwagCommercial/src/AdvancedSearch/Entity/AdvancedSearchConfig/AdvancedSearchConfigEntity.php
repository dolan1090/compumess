<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate\AdvancedSearchConfigFieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

#[Package('buyers-experience')]
class AdvancedSearchConfigEntity extends Entity
{
    use EntityIdTrait;

    protected string $salesChannelId;

    protected bool $esEnabled;

    protected bool $andLogic;

    protected int $minSearchLength;

    /**
     * @var array<string, array<string, int|null>>
     */
    protected array $hitCount;

    protected ?SalesChannelEntity $salesChannel = null;

    protected ?AdvancedSearchConfigFieldCollection $fields = null;

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getEsEnabled(): bool
    {
        return $this->esEnabled;
    }

    public function setEsEnabled(bool $esEnabled): void
    {
        $this->esEnabled = $esEnabled;
    }

    public function getAndLogic(): bool
    {
        return $this->andLogic;
    }

    public function setAndLogic(bool $andLogic): void
    {
        $this->andLogic = $andLogic;
    }

    public function getMinSearchLength(): int
    {
        return $this->minSearchLength;
    }

    public function setMinSearchLength(int $minSearchLength): void
    {
        $this->minSearchLength = $minSearchLength;
    }

    public function getSalesChannel(): ?SalesChannelEntity
    {
        return $this->salesChannel;
    }

    public function setSalesChannel(SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    public function getFields(): ?AdvancedSearchConfigFieldCollection
    {
        return $this->fields;
    }

    public function setFields(AdvancedSearchConfigFieldCollection $fields): void
    {
        $this->fields = $fields;
    }

    public function getApiAlias(): string
    {
        return 'advanced_search_config';
    }

    /**
     * @return array<string, array<string, int|null>>
     */
    public function getHitCount(): array
    {
        return $this->hitCount;
    }

    /**
     * @param array<string, array<string, int|null>> $hitCount
     */
    public function setHitCount(array $hitCount): void
    {
        $this->hitCount = $hitCount;
    }
}

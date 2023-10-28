<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\Aggregate;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\CustomField\CustomFieldEntity;

#[Package('buyers-experience')]
class AdvancedSearchConfigFieldEntity extends Entity
{
    use EntityIdTrait;

    protected string $configId;

    protected string $entity;

    protected string $field;

    protected bool $tokenize;

    protected bool $searchable;

    protected int $ranking;

    protected ?string $customFieldId;

    protected ?AdvancedSearchConfigEntity $config = null;

    protected ?CustomFieldEntity $customField = null;

    public function getConfigId(): string
    {
        return $this->configId;
    }

    public function setConfigId(string $configId): void
    {
        $this->configId = $configId;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function setField(string $field): void
    {
        $this->field = $field;
    }

    public function getTokenize(): bool
    {
        return $this->tokenize;
    }

    public function setTokenize(bool $tokenize): void
    {
        $this->tokenize = $tokenize;
    }

    public function getSearchable(): bool
    {
        return $this->searchable;
    }

    public function setSearchable(bool $searchable): void
    {
        $this->searchable = $searchable;
    }

    public function getRanking(): int
    {
        return $this->ranking;
    }

    public function setRanking(int $ranking): void
    {
        $this->ranking = $ranking;
    }

    public function getConfig(): ?AdvancedSearchConfigEntity
    {
        return $this->config;
    }

    public function setConfig(AdvancedSearchConfigEntity $config): void
    {
        $this->config = $config;
    }

    public function getCustomFieldId(): ?string
    {
        return $this->customFieldId;
    }

    public function setCustomFieldId(?string $customFieldId): void
    {
        $this->customFieldId = $customFieldId;
    }

    public function getCustomField(): ?CustomFieldEntity
    {
        return $this->customField;
    }

    public function setCustomField(?CustomFieldEntity $customField): void
    {
        $this->customField = $customField;
    }

    public function getApiAlias(): string
    {
        return 'advanced_search_config_field';
    }
}

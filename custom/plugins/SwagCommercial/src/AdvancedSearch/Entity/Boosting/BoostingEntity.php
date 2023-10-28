<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\Boosting;

use Shopware\Commercial\AdvancedSearch\Entity\AdvancedSearchConfig\AdvancedSearchConfigEntity;
use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\EntityStreamEntity;
use Shopware\Core\Content\ProductStream\ProductStreamEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class BoostingEntity extends Entity
{
    use EntityIdTrait;

    protected float $boost;

    protected ?\DateTimeInterface $validFrom = null;

    protected ?\DateTimeInterface $validTo = null;

    protected string $configId;

    protected bool $active;

    protected string $name;

    protected string $productStreamId;

    protected string $entityStreamId;

    protected ?AdvancedSearchConfigEntity $config = null;

    protected ?EntityStreamEntity $entityStream = null;

    protected ?ProductStreamEntity $productStream = null;

    public function getConfigId(): string
    {
        return $this->configId;
    }

    public function setConfigId(string $configId): void
    {
        $this->configId = $configId;
    }

    public function getProductStreamId(): string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getEntityStreamId(): string
    {
        return $this->entityStreamId;
    }

    public function setEntityStreamId(string $entityStreamId): void
    {
        $this->entityStreamId = $entityStreamId;
    }

    public function getBoost(): float
    {
        return $this->boost;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getConfig(): ?AdvancedSearchConfigEntity
    {
        return $this->config;
    }

    public function setConfig(AdvancedSearchConfigEntity $config): void
    {
        $this->config = $config;
    }

    public function getProductStream(): ?ProductStreamEntity
    {
        return $this->productStream;
    }

    public function setProductStream(ProductStreamEntity $productStream): void
    {
        $this->productStream = $productStream;
    }

    public function getEntityStream(): ?EntityStreamEntity
    {
        return $this->entityStream;
    }

    public function setEntityStream(EntityStreamEntity $entityStream): void
    {
        $this->entityStream = $entityStream;
    }
}

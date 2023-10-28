<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup;

use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseCollection;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class WarehouseGroupEntity extends Entity
{
    use EntityIdTrait;

    protected string $name;

    protected ?string $description = null;

    protected int $priority = 1;

    protected ?string $ruleId = null;

    protected ?RuleEntity $rule = null;

    protected ?WarehouseCollection $warehouses = null;

    protected ?ProductCollection $products = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->name;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority = 1): void
    {
        $this->priority = $priority;
    }

    public function getRuleId(): ?string
    {
        return $this->ruleId;
    }

    public function setRuleId(?string $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getRule(): ?RuleEntity
    {
        return $this->rule;
    }

    public function setRule(?RuleEntity $ruleEntity): void
    {
        $this->rule = $ruleEntity;
    }

    public function getWarehouses(): ?WarehouseCollection
    {
        return $this->warehouses;
    }

    public function setWarehouses(WarehouseCollection $warehouses): void
    {
        $this->warehouses = $warehouses;
    }

    public function getProducts(): ?ProductCollection
    {
        return $this->products;
    }

    public function setProducts(ProductCollection $products): void
    {
        $this->products = $products;
    }
}

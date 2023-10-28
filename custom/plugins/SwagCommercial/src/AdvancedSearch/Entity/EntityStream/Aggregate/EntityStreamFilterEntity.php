<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Entity\EntityStream\Aggregate;

use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\EntityStreamEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class EntityStreamFilterEntity extends Entity
{
    use EntityIdTrait;

    protected string $type;

    protected ?string $field;

    protected ?string $operator;

    protected ?string $value;

    protected string $entityStreamId;

    protected ?string $parentId;

    protected ?EntityStreamEntity $entityStream = null;

    protected ?EntityStreamFilterCollection $queries = null;

    protected ?EntityStreamFilterEntity $parent = null;

    protected ?int $position;

    /**
     * @var array<string, mixed>|null
     */
    protected ?array $parameters = null;

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): void
    {
        $this->field = $field;
    }

    public function getOperator(): ?string
    {
        return $this->operator;
    }

    public function setOperator(?string $operator): void
    {
        $this->operator = $operator;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): void
    {
        $this->value = $value;
    }

    public function getEntityStreamId(): string
    {
        return $this->entityStreamId;
    }

    public function setEntityStreamId(string $entityStreamId): void
    {
        $this->entityStreamId = $entityStreamId;
    }

    public function getParentId(): ?string
    {
        return $this->parentId;
    }

    public function setParentId(?string $parentId): void
    {
        $this->parentId = $parentId;
    }

    public function getEntityStream(): ?EntityStreamEntity
    {
        return $this->entityStream;
    }

    public function setEntityStream(?EntityStreamEntity $entityStream): void
    {
        $this->entityStream = $entityStream;
    }

    public function getQueries(): ?EntityStreamFilterCollection
    {
        return $this->queries;
    }

    public function setQueries(EntityStreamFilterCollection $queries): void
    {
        $this->queries = $queries;
    }

    public function getParent(): ?EntityStreamFilterEntity
    {
        return $this->parent;
    }

    public function setParent(?EntityStreamFilterEntity $parent): void
    {
        $this->parent = $parent;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param array<string, mixed>|null $parameters
     */
    public function setParameters(?array $parameters): void
    {
        $this->parameters = $parameters;
    }
}

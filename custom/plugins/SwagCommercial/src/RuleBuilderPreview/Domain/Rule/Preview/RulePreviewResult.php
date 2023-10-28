<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

/**
 * @internal
 */
#[Package('business-ops')]
final class RulePreviewResult extends Struct
{
    protected string $name = '';

    protected bool $match = false;

    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    protected ?string $ruleReferenceId = null;

    protected RulePreviewResultCollection $rules;

    protected ?LineItem $lineItem = null;

    protected bool $isEvaluated = false;

    public function __construct()
    {
        $this->rules = new RulePreviewResultCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isMatch(): bool
    {
        return $this->match;
    }

    public function setMatch(bool $match): void
    {
        $this->match = $match;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getRuleReferenceId(): ?string
    {
        return $this->ruleReferenceId;
    }

    public function setRuleReferenceId(?string $ruleReferenceId): void
    {
        $this->ruleReferenceId = $ruleReferenceId;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    public function getRules(): RulePreviewResultCollection
    {
        return $this->rules;
    }

    public function setRules(RulePreviewResultCollection $rules): void
    {
        $this->rules = $rules;
    }

    public function getLineItem(): ?LineItem
    {
        return $this->lineItem;
    }

    public function setLineItem(?LineItem $lineItem): void
    {
        $this->lineItem = $lineItem;
    }

    public function isEvaluated(): bool
    {
        return $this->isEvaluated;
    }

    public function setIsEvaluated(bool $isEvaluated): void
    {
        $this->isEvaluated = $isEvaluated;
    }
}

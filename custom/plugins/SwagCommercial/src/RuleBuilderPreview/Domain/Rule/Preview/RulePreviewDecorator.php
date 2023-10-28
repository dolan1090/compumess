<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Rule\Container\Container;
use Shopware\Core\Framework\Rule\Container\ContainerInterface;
use Shopware\Core\Framework\Rule\Container\FilterRule;
use Shopware\Core\Framework\Rule\Container\MatchAllLineItemsRule;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;
use Symfony\Component\Validator\Constraint;

/**
 * @internal
 */
#[Package('business-ops')]
final class RulePreviewDecorator extends Rule
{
    private RulePreviewResult $result;

    public function __construct(
        private readonly Rule $decoratedRule,
        private readonly ?RulePreviewDecorator $parentRule = null
    ) {
        $this->result = new RulePreviewResult();
        parent::__construct();
    }

    public function getName(): string
    {
        return $this->decoratedRule->getName();
    }

    public function match(RuleScope $scope): bool
    {
        if ($scope instanceof LineItemScope) {
            $this->result = new RulePreviewResult();
        }

        if ($this->result->isEvaluated()) {
            return $this->result->isMatch();
        }

        $match = $this->decoratedRule->match($scope);

        /** @var RuleReferenceIdStruct|null $referenceIdStruct */
        $referenceIdStruct = $this->decoratedRule->getExtension('referenceId');

        $this->result->setName($this->getName());
        $this->result->setMatch($match);
        $this->result->setParameters($this->getParameters());
        $this->result->setRuleReferenceId($referenceIdStruct ? $referenceIdStruct->getId() : null);
        $this->result->setLineItem($this->getLineItem($scope, $match));
        $this->result->setIsEvaluated(true);

        $this->parentRule?->getResult()->getRules()->add($this->result);

        return $match;
    }

    public function mockMatch(RuleScope $scope): void
    {
        $this->match($scope);

        if ($this->decoratedRule instanceof FilterRule || $this->decoratedRule instanceof MatchAllLineItemsRule) {
            return;
        }

        /** @var RulePreviewDecorator $rule */
        foreach ($this->getRules() as $rule) {
            $rule->mockMatch($scope);
        }
    }

    /**
     * @return array<string, Constraint[]>
     */
    public function getConstraints(): array
    {
        return $this->decoratedRule->getConstraints();
    }

    /**
     * @return Rule[]
     */
    public function getRules(): array
    {
        if ($this->decoratedRule instanceof Container) {
            return $this->decoratedRule->getRules();
        }

        return [];
    }

    public function getResult(): RulePreviewResult
    {
        return $this->result;
    }

    /**
     * @return array<string, mixed>
     */
    private function getParameters(): array
    {
        $vars = $this->decoratedRule->getVars();
        unset($vars['rules'], $vars['_name'], $vars['extensions'], $vars['filter']);

        return $vars;
    }

    private function getLineItem(RuleScope $scope, bool $isMatch): ?LineItem
    {
        if ($this->decoratedRule instanceof ContainerInterface) {
            return null;
        }

        if ($scope instanceof LineItemScope) {
            return $scope->getLineItem();
        }

        if (!$scope instanceof PreviewRuleScope || !$isMatch) {
            return null;
        }

        $classShortName = (new \ReflectionClass($this->decoratedRule))->getShortName();

        if (\mb_substr($classShortName, 0, 8) !== 'LineItem') {
            return null;
        }

        foreach ($scope->getCart()->getLineItems()->getFlat() as $lineItem) {
            $scope = new LineItemScope($lineItem, $scope->getSalesChannelContext());

            if ($this->decoratedRule->match($scope)) {
                return $lineItem;
            }
        }

        return null;
    }
}

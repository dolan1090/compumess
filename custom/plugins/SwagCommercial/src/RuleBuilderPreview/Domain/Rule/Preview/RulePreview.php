<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Rule\Collector\RuleConditionRegistry;
use Shopware\Core\Framework\Rule\Container\AndRule;
use Shopware\Core\Framework\Rule\Container\Container;
use Shopware\Core\Framework\Rule\Container\ContainerInterface;
use Shopware\Core\Framework\Rule\Container\FilterRule;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\ScriptRule;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 *
 * @internal
 */
#[Package('business-ops')]
class RulePreview implements ResetInterface
{
    private ConstraintViolationList $violationList;

    /**
     * @internal
     *
     * @param \Shopware\Core\Checkout\Cart\CartDataCollectorInterface[] $collectors
     */
    public function __construct(
        private readonly OrderConverter $orderConverter,
        private readonly RuleConditionRegistry $ruleConditionRegistry,
        private readonly iterable $collectors,
        private readonly ValidatorInterface $validator,
        private readonly EntityRepository $appScriptConditionRepository
    ) {
    }

    public function reset(): void
    {
        $this->violationList = new ConstraintViolationList();
    }

    /**
     * @param array<int, array<string, mixed>> $conditions
     */
    public function preview(
        OrderEntity $order,
        array $conditions,
        Context $context,
        ?\DateTimeImmutable $dateTime = null,
        bool $skipMock = false
    ): RulePreviewResultCollection {
        if (!License::get('RULE_BUILDER-1967308')) {
            throw new LicenseExpiredException();
        }

        $this->reset();

        $rule = new AndRule($this->buildPayload($conditions, $context));

        if ($this->violationList->count() > 0) {
            throw new ConstraintViolationException($this->violationList, $conditions);
        }

        $scope = $this->buildScope($order, $context, $dateTime);

        return new RulePreviewResultCollection([$this->getRulePreviewResults($scope, $rule, $skipMock)]);
    }

    private function getRulePreviewResults(PreviewRuleScope $scope, Rule $rule, bool $skipMock): RulePreviewResult
    {
        $rule = $this->wrapRulesInDecorator($rule);

        if ($skipMock) {
            $rule->match($scope);
        } else {
            $rule->mockMatch($scope);
        }

        return $rule->getResult();
    }

    private function wrapRulesInDecorator(Rule $rule, ?RulePreviewDecorator $parent = null): RulePreviewDecorator
    {
        $wrappedRule = new RulePreviewDecorator($rule, $parent);

        if ($rule instanceof Container || $rule instanceof FilterRule) {
            $wrappedRules = [];

            foreach ($rule->getRules() as $containedRule) {
                $wrappedRules[] = $this->wrapRulesInDecorator($containedRule, $wrappedRule);
            }

            $rule->setRules($wrappedRules);
        }

        return $wrappedRule;
    }

    private function buildScope(OrderEntity $order, Context $context, ?\DateTimeImmutable $dateTime = null): PreviewRuleScope
    {
        $salesChannelContext = $this->orderConverter->assembleSalesChannelContext($order, $context);
        $cart = $this->orderConverter->convertToCart($order, $salesChannelContext->getContext());

        $cartBehavior = new CartBehavior($salesChannelContext->getPermissions());
        /** @var CartDataCollectorInterface $collector */
        foreach ($this->collectors as $collector) {
            $collector->collect($cart->getData(), $cart, $salesChannelContext, $cartBehavior);
        }

        return new PreviewRuleScope($cart, $salesChannelContext, $dateTime);
    }

    /**
     * @param array<int, array<string, mixed>> $conditions
     *
     * @return Rule[]
     */
    private function buildPayload(array $conditions, Context $context): array
    {
        $payload = [];

        foreach ($conditions as $condition) {
            if ($this->buildTypeViolation($condition) || !\is_string($condition['type'])) {
                continue;
            }

            $ruleClass = $this->ruleConditionRegistry->getRuleClass($condition['type']);
            $rule = new $ruleClass();

            $value = \is_array($condition['value']) ? $condition['value'] : [];
            if ($rule instanceof ScriptRule) {
                $this->enrichScriptCondition($rule, $condition, $context);
            } else {
                $rule->assign($value);
            }

            $id = \is_string($condition['id']) ? $condition['id'] : '';

            $rule->addExtension('referenceId', new RuleReferenceIdStruct($id));
            if (\method_exists($rule, '__wakeup')) {
                $rule->__wakeup();
            }

            if ($rule instanceof ContainerInterface) {
                /** @var array<int, array<string, mixed>> $children */
                $children = \is_array($condition['children']) ? $condition['children'] : [];
                $childRules = $this->buildPayload($children, $context);
                foreach ($childRules as $childRule) {
                    $rule->addRule($childRule);
                }
            }

            foreach ($rule->getConstraints() as $fieldName => $validations) {
                $this->violationList->addAll(
                    $this->validator
                        ->startContext()
                        ->atPath(\sprintf('rule_condition/%s/value/%s', $id, $fieldName))
                        ->validate($value[$fieldName] ?? null, $validations)
                        ->getViolations()
                );
            }

            $payload[] = $rule;
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function buildTypeViolation(array $condition): bool
    {
        $code = null;
        $messageTemplate = '';
        $parameters = [];
        $type = \is_string($condition['type']) ? $condition['type'] : null;

        if (empty($type)) {
            $code = 'CONTENT__MISSING_RULE_TYPE_EXCEPTION';
            $messageTemplate = 'Your condition is missing a type.';
        }

        if (!empty($type) && !$this->ruleConditionRegistry->has($type)) {
            $code = 'CONTENT__INVALID_RULE_TYPE_EXCEPTION';
            $messageTemplate = 'This {{ value }} is not a valid condition type.';
            $parameters = ['{{ value }}' => $type];
        }

        if ($code === null) {
            return false;
        }

        $id = \is_string($condition['id']) ? $condition['id'] : '';

        $this->violationList->add(
            new ConstraintViolation(
                \str_replace(\array_keys($parameters), \array_values($parameters), $messageTemplate),
                $messageTemplate,
                $parameters,
                null,
                \sprintf('rule_condition/%s/type', $id),
                null,
                null,
                $code
            )
        );

        return true;
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function enrichScriptCondition(ScriptRule $rule, array $condition, Context $context): void
    {
        if (!isset($condition['scriptId']) || !\is_string($condition['scriptId'])) {
            return;
        }

        $script = $this->appScriptConditionRepository->search(
            new Criteria([$condition['scriptId']]),
            $context
        )->get($condition['scriptId']);

        if (!$script instanceof AppScriptConditionEntity) {
            return;
        }

        $constraints = $script->getConstraints();
        if (\is_array($constraints)) {
            $rule->setConstraints($constraints);
        }

        $rule->assign([
            'identifier' => $condition['scriptId'],
            'values' => $condition['value'] ?? [],
            'script' => $script->getScript(),
            'debug' => true,
        ]);
    }
}

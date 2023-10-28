<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Framework\Checkout\Cart\Rule;

use Shopware\Commercial\CustomPricing\Subscriber\ProductSubscriber;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Rule\RuleScope;

/**
 * @internal
 */
#[Package('inventory')]
class CustomPricesRule extends Rule
{
    final public const RULE_NAME = 'custom prices';

    /**
     * {@inheritDoc}
     */
    public function match(RuleScope $scope): bool
    {
        return $scope->getContext()->hasState(ProductSubscriber::CUSTOM_PRICING_STATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getConstraints(): array
    {
        return [];
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Framework\Checkout\Cart;

use Shopware\Commercial\CustomPricing\Framework\Checkout\Cart\Rule\CustomPricesRule;
use Shopware\Commercial\CustomPricing\Subscriber\ProductSubscriber;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Cart\AbstractRuleLoader;
use Shopware\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Content\Rule\RuleEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class CustomPricingRuleLoaderDecorator extends AbstractRuleLoader
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractRuleLoader $decorated)
    {
    }

    public function getDecorated(): AbstractRuleLoader
    {
        return $this->decorated;
    }

    public function load(Context $context): RuleCollection
    {
        $ruleCollection = $this->decorated->load($context);
        if (!License::get('CUSTOM_PRICES-4458487')) {
            return $ruleCollection;
        }

        $rule = new RuleEntity();
        $conditionCollection = new RuleConditionCollection();
        $rule->setId(ProductSubscriber::CUSTOMER_PRICE_RULE);
        $rule->setName('Customer has custom prices');
        $rule->setConditions($conditionCollection);
        $rule->setPayload(new CustomPricesRule());
        $rule->setPriority(0);

        $ruleCollection->add($rule);

        return $ruleCollection;
    }
}

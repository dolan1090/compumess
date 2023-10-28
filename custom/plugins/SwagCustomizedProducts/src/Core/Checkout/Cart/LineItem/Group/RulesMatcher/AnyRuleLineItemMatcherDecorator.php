<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Core\Checkout\Cart\LineItem\Group\RulesMatcher;

use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemGroupDefinition;
use Shopware\Core\Checkout\Cart\LineItem\Group\RulesMatcher\AbstractAnyRuleLineItemMatcher;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class AnyRuleLineItemMatcherDecorator extends AbstractAnyRuleLineItemMatcher
{
    public function __construct(private readonly AbstractAnyRuleLineItemMatcher $decorated)
    {
    }

    public function getDecorated(): AbstractAnyRuleLineItemMatcher
    {
        return $this->decorated;
    }

    public function isMatching(LineItemGroupDefinition $groupDefinition, LineItem $item, SalesChannelContext $context): bool
    {
        // no rules mean OK
        if ($groupDefinition->getRules()->count() <= 0) {
            return true;
        }

        // Ensure functionality of custom products
        if ($item->getType() === CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE) {
            $productLineItem = $item->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();

            if ($productLineItem !== null) {
                $item = $productLineItem;
            }
        }

        // if we have rules, make sure
        // they are connected using an OR condition
        $scope = new LineItemScope($item, $context);

        foreach ($groupDefinition->getRules() as $rule) {
            $rootCondition = $rule->getPayload();

            // if any rule matches, return OK
            if ($rootCondition instanceof Rule && $rootCondition->match($scope)) {
                return true;
            }
        }

        return false;
    }
}

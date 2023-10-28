<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Cart\LineItem\Group\RulesMatcher;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemGroupDefinition;
use Shopware\Core\Checkout\Cart\LineItem\Group\RulesMatcher\AbstractAnyRuleLineItemMatcher;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Content\Rule\RuleCollection;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Traits\RulesTestFixtureBehaviour;
use Swag\CustomizedProducts\Core\Checkout\Cart\LineItem\Group\RulesMatcher\AnyRuleLineItemMatcherDecorator;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;

class AnyRuleLineItemMatcherDecoratorTest extends TestCase
{
    use IntegrationTestBehaviour;
    use RulesTestFixtureBehaviour;

    public function testGetDecorated(): void
    {
        /**
         * @var AbstractAnyRuleLineItemMatcher|MockObject $ruleMatcherMock
         */
        $ruleMatcherMock = $this->createMock(AbstractAnyRuleLineItemMatcher::class);
        $ruleMatcherDecorator = new AnyRuleLineItemMatcherDecorator($ruleMatcherMock);

        static::assertSame($ruleMatcherMock, $ruleMatcherDecorator->getDecorated());
    }

    /**
     * @dataProvider lineItemProvider
     */
    public function testMatching(bool $expectedCustom, bool $expectedProduct, array $productIds = []): void
    {
        /**
         * @var AbstractAnyRuleLineItemMatcher|MockObject $ruleMatcherMock
         */
        $ruleMatcherMock = $this->createMock(AbstractAnyRuleLineItemMatcher::class);
        $ruleMatcherDecorator = new AnyRuleLineItemMatcherDecorator($ruleMatcherMock);

        $lineItem = new LineItem('Product', LineItem::PRODUCT_LINE_ITEM_TYPE);
        $lineItem->setReferencedId($lineItem->getId());

        $customProductsLineItem = new LineItem(
            'Custom',
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customProductsLineItem->setChildren(new LineItemCollection([
            new LineItem('Child', LineItem::PRODUCT_LINE_ITEM_TYPE, 'Ref'),
        ]));

        $ruleCollection = $this->buildProductsRule($productIds);

        $group = new LineItemGroupDefinition('test-1', 'COUNT', 1, 'PRICE_ASC', $ruleCollection);
        $context = $this->createMock(SalesChannelContext::class);

        static::assertEquals(
            $expectedCustom,
            $ruleMatcherDecorator->isMatching($group, $customProductsLineItem, $context)
        );
        static::assertEquals($expectedProduct, $ruleMatcherDecorator->isMatching($group, $lineItem, $context));
    }

    public function lineItemProvider(): iterable
    {
        yield 'Matching custom product in group' => [true, false, ['Ref']];
        yield 'Matching custom product and product in group' => [true, true, ['Ref', 'Product']];
        yield 'Matching product in group' => [false, true, ['Product']];
        yield 'Matching without group' => [true, true];
        yield 'Matching generated id of custom product' => [false, false, ['Wrong']];
    }

    private function buildProductsRule(array $ids = []): RuleCollection
    {
        $items = \count($ids) > 0 ? [$this->buildRuleEntity($this->getProductsRule($ids))] : [];

        return new RuleCollection($items);
    }
}

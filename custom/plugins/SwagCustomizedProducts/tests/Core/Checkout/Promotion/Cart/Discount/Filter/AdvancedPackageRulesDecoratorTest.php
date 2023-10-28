<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Promotion\Cart\Discount\Filter;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemFlatCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Rule\LineItemRule;
use Shopware\Core\Checkout\Promotion\Aggregate\PromotionDiscount\PromotionDiscountEntity;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\Filter\AdvancedPackageRules;
use Shopware\Core\Checkout\Promotion\Cart\Discount\Filter\SetGroupScopeFilter;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Test\IdsCollection;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\Test\TestDefaults;
use Shopware\Tests\Unit\Core\Checkout\Cart\LineItem\Group\Helpers\Traits\RulesTestFixtureBehaviour;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Checkout\Promotion\Cart\Discount\Filter\AdvancedPackageRulesDecorator;

class AdvancedPackageRulesDecoratorTest extends TestCase
{
    use IntegrationTestBehaviour;
    use RulesTestFixtureBehaviour;

    private AbstractSalesChannelContextFactory $salesChannelContextFactory;

    private IdsCollection $ids;

    public function setUp(): void
    {
        $this->salesChannelContextFactory = $this->getContainer()->get(SalesChannelContextFactory::class);
        $this->ids = new IdsCollection();
    }

    public function testGetDecorated(): void
    {
        /**
         * @var SetGroupScopeFilter|MockObject $packageRulesMock
         */
        $packageRulesMock = $this->createMock(SetGroupScopeFilter::class);
        $packageRulesDecorator = new AdvancedPackageRulesDecorator($packageRulesMock);

        static::assertSame($packageRulesMock, $packageRulesDecorator->getDecorated());
    }

    public function testFilterWithoutCustomProductReturnsOriginalItems(): void
    {
        $discountPackage = $this->getDiscountPackageCollection([
            new LineItem('test', LineItem::PRODUCT_LINE_ITEM_TYPE),
        ]);
        /**
         * @var SetGroupScopeFilter|MockObject $packageRulesMock
         */
        $packageRulesMock = $this->createMock(SetGroupScopeFilter::class);
        $packageRulesMock->method('filter')
            ->willReturn($discountPackage);

        $decorated = new AdvancedPackageRulesDecorator($packageRulesMock);
        $discountItem = $this->getDiscountLineItem();
        $salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::SALES_CHANNEL
        );

        $result = $decorated->filter($discountItem, $discountPackage, $salesChannelContext);
        static::assertEquals($discountPackage, $result);
    }

    /**
     * @dataProvider packagesRuleProvider
     */
    public function testFilterPackagesWithRule(string $operator, int $count): void
    {
        $productLineItem = new LineItem($this->ids->get('p1'), LineItem::PRODUCT_LINE_ITEM_TYPE, $this->ids->get('p1'));
        $customProductLineItem1 = $this->getCustomProductLineItem('1');
        $customProductLineItem2 = $this->getCustomProductLineItem('2');

        $discountPackage = $this->getDiscountPackageCollection([
            $productLineItem,
            $customProductLineItem1,
            $customProductLineItem2,
        ]);

        $filter = new LineItemRule($operator, [$this->ids->get('cp-child-1')]);
        $discountItem = $this->getDiscountLineItem($filter);

        $salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::SALES_CHANNEL
        );

        $decorated = new AdvancedPackageRulesDecorator(new AdvancedPackageRules());
        $result = $decorated->filter($discountItem, $discountPackage, $salesChannelContext)->getElements();

        static::assertCount($count, $result);

        if ($operator === Rule::OPERATOR_EQ) {
            $this->assertDiscountPackage($result[0], 'cp-1');
        } else {
            $this->assertDiscountPackage($result[0], 'p1');
            $this->assertDiscountPackage($result[1], 'cp-2');
        }
    }

    public function testFilterPackageWithoutRule(): void
    {
        $customizedProductLineItem = $this->getCustomProductLineItem('1');

        $discountPackage = $this->getDiscountPackageCollection([$customizedProductLineItem]);

        /**
         * @var SetGroupScopeFilter|MockObject $advancedPackageMock
         */
        $advancedPackageMock = $this->createMock(SetGroupScopeFilter::class);
        $advancedPackageMock->method('filter')
            ->willReturn($discountPackage);

        $decorated = new AdvancedPackageRulesDecorator($advancedPackageMock);

        $discountItem = $this->getDiscountLineItem();
        $salesChannelContext = $this->salesChannelContextFactory->create(
            Uuid::randomHex(),
            TestDefaults::SALES_CHANNEL
        );

        $result = $decorated->filter($discountItem, $discountPackage, $salesChannelContext);

        static::assertCount(1, $result);

        $customizedProductDiscountPackage = $result->last();
        static::assertNotNull($customizedProductDiscountPackage);

        $customizedProductLineItemQuantity = $customizedProductDiscountPackage->getMetaData()->first();

        static::assertNotNull($customizedProductLineItemQuantity);
        static::assertSame($this->ids->get('cp-1'), $customizedProductLineItemQuantity->getLineItemId());
    }

    public function packagesRuleProvider(): iterable
    {
        yield 'is one of' => [Rule::OPERATOR_EQ, 1];
        yield 'is none of' => [Rule::OPERATOR_NEQ, 2];
    }

    private function getCustomProductLineItem(string $name): LineItem
    {
        $lineItem = new LineItem(
            $this->ids->get('cp-' . $name),
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE,
            $this->ids->get('cp-' . $name),
        );
        $lineItem->addChild(
            new LineItem(
                $this->ids->get('cp-child-' . $name),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                $this->ids->get('cp-child-' . $name)
            )
        );

        return $lineItem;
    }

    private function getDiscountLineItem(?Rule $filter = null): DiscountLineItem
    {
        $priceDefinition = new AbsolutePriceDefinition(-10);
        if ($filter !== null) {
            $priceDefinition = new AbsolutePriceDefinition(-10, $filter);
        }

        return new DiscountLineItem('10€ Discount', $priceDefinition, [
            'discountScope' => PromotionDiscountEntity::SCOPE_CART,
            'discountType' => PromotionDiscountEntity::TYPE_ABSOLUTE,
            'filter' => [
                'sorterKey' => 'PRICE_ASC',
                'applierKey' => 'ALL',
                'usageKey' => 'UNLIMITED',
            ],
        ], null);
    }

    private function getDiscountPackageCollection(array $lineItems = []): DiscountPackageCollection
    {
        $collection = new DiscountPackageCollection();

        if (\count($lineItems) <= 0) {
            $collection->add(new DiscountPackage(new LineItemQuantityCollection()));

            return $collection;
        }

        foreach ($lineItems as $lineItem) {
            $lineItem->setStackable(true);

            $itmQty = new LineItemQuantity($lineItem->getId(), $lineItem->getQuantity());

            $package = new DiscountPackage(new LineItemQuantityCollection([$itmQty]));
            $package->setCartItems(new LineItemFlatCollection([$lineItem]));

            $collection->add($package);
        }

        return $collection;
    }

    private function assertDiscountPackage(?DiscountPackage $package, string $name): void
    {
        static::assertNotNull($package);

        $lineItemQty = $package->getMetaData()->first();
        static::assertNotNull($lineItemQty);

        static::assertSame($lineItemQty->getLineItemId(), $this->ids->get($name));
    }
}

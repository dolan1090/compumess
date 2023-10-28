<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Content\MailTemplate\Service\Event;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\AbsolutePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\MailTemplate\MailTemplateTypes;
use Shopware\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Core\Content\MailTemplate\Service\Event\OrderConfirmationSubscriber;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\DateTime;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Timestamp;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class OrderConfirmationSubscriberTest extends TestCase
{
    use ServicesTrait;

    final public const DEFAULT_LABEL = 'Label';
    final public const TEST_VALUE = 'test_value';

    /**
     * @var OrderConfirmationSubscriber
     */
    private $orderConfirmationSubscriber;

    protected function setUp(): void
    {
        $this->orderConfirmationSubscriber = $this->getContainer()->get(OrderConfirmationSubscriber::class);
    }

    public function testItEarlyReturnsIfNotOrderConfirmationMail(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getData')
            ->willReturn(
                [
                    'templateId' => Uuid::randomHex(),
                ]
            );
        $event->expects(static::never())
            ->method('getTemplateData');

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testItEarlyReturnsIfNoTemplateIdIsInTheEventData(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $event->method('getData')
            ->willReturn([]);
        $event->expects(static::never())
            ->method('getTemplateData');

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testItEarlyReturnsIfNoOrderInTemplateData(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $validConfirmationMailTemplateId = $this->getValidMailTemplateId();
        static::assertNotNull($validConfirmationMailTemplateId);
        $event->method('getData')
            ->willReturn(
                [
                    'templateId' => $validConfirmationMailTemplateId,
                ]
            );
        $event->expects(static::once())
            ->method('getTemplateData')
            ->willReturn(
                [
                    'order' => null,
                ]
            );

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testItEarlyReturnsIfNoOrderLineItems(): void
    {
        /**
         * @var MockObject|MailBeforeValidateEvent $event
         */
        $event = $this->getMockBuilder(MailBeforeValidateEvent::class)->disableOriginalConstructor()->getMock();
        $validConfirmationMailTemplateId = $this->getValidMailTemplateId();
        static::assertNotNull($validConfirmationMailTemplateId);
        $event->method('getData')
            ->willReturn(
                [
                    'templateId' => $validConfirmationMailTemplateId,
                ]
            );

        $order = $this->getMockBuilder(OrderEntity::class)->getMock();
        $order->expects(static::once())
            ->method('getLineItems')
            ->willReturn(null);
        $event->expects(static::once())
            ->method('getTemplateData')
            ->willReturn(
                [
                    'order' => $order,
                ]
            );

        $this->orderConfirmationSubscriber->__invoke($event);
    }

    public function testThatOtherPriceDefinitionLineItemsDontGetChanged(): void
    {
        $templateLineItemId = Uuid::randomHex();
        $customizedProductTemplateLineItem = new OrderLineItemEntity();
        $customizedProductTemplateLineItem->setId($templateLineItemId);
        $customizedProductTemplateLineItem->setPosition(1);
        $customizedProductTemplateLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductTemplateLineItem->setLabel('TestTemplate');
        $customizedProductOptionLineItem = new OrderLineItemEntity();
        $customizedProductOptionLineItem->setId(Uuid::randomHex());
        $customizedProductOptionLineItem->setPosition(2);
        $customizedProductOptionLineItem->setParentId($templateLineItemId);
        $customizedProductOptionLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductOptionLineItem->setPriceDefinition(new AbsolutePriceDefinition(19.99));
        $customizedProductOptionLineItem->setQuantity(5);
        $customizedProductOptionLineItem->setLabel('TestOption');
        $orderLineItems = new OrderLineItemCollection(
            [
                $customizedProductTemplateLineItem,
                $customizedProductOptionLineItem,
            ]
        );
        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(
            $orderLineItems
        );
        $event = new MailBeforeValidateEvent(
            [
                'templateId' => $this->getValidMailTemplateId(),
            ],
            Context::createDefaultContext(),
            [
                'order' => $order,
            ]
        );
        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertSame(5, $customizedProductOptionLineItem->getQuantity());
    }

    public function dataProviderMailTemplate(): array
    {
        return [
            [
                MailTemplateTypes::MAILTYPE_ORDER_CONFIRM,
            ],
            [
                MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_TRANSACTION_STATE_PAID,
            ],
            [
                MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_TRANSACTION_STATE_CANCELLED,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderMailTemplate
     */
    public function testThatNestingIsCorrect(string $mailTemplateType): void
    {
        [$templateLineItem, $productLineItem, $optionLineItem, $valueItem] = $this->getNestedStructure();
        $productLineItemId = $productLineItem->getId();
        $optionLineItemId = $optionLineItem->getId();

        $orderLineItems = new OrderLineItemCollection(
            [
                $templateLineItem,
                $productLineItem,
                $optionLineItem,
                $valueItem,
            ]
        );

        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(
            $orderLineItems
        );
        $event = new MailBeforeValidateEvent(
            [
                'templateId' => $this->getValidMailTemplateId($mailTemplateType),
            ],
            Context::createDefaultContext(),
            [
                'order' => $order,
            ]
        );

        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertNull($productLineItem->getParentId());
        static::assertSame($productLineItemId, $optionLineItem->getParentId());
        static::assertSame($optionLineItemId, $valueItem->getParentId());
        static::assertNotNull($order->getLineItems());
        static::assertCount(3, $order->getLineItems());
    }

    public function testThatOneTimeSurchargeSetQuantityCorrect(): void
    {
        [$templateLineItem, $productLineItem, $optionLineItem, $valueItem] = $this->getNestedStructure();

        $quantityBefore = $optionLineItem->getQuantity();
        $payload = $optionLineItem->getPayload();
        $optionLineItem->setPayload(['isOneTimeSurcharge' => true]);

        $orderLineItems = new OrderLineItemCollection(
            [
                $templateLineItem,
                $productLineItem,
                $optionLineItem,
                $valueItem,
            ]
        );

        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(
            $orderLineItems
        );
        $event = new MailBeforeValidateEvent(
            [
                'templateId' => $this->getValidMailTemplateId(),
            ],
            Context::createDefaultContext(),
            [
                'order' => $order,
            ]
        );

        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertNotSame($quantityBefore, $optionLineItem->getQuantity());
        static::assertSame(1, $optionLineItem->getQuantity());
    }

    public function testThatPercentagePriceDefinitionLineItemsGetChanged(): void
    {
        $templateLineItemId = Uuid::randomHex();
        $customizedProductTemplateLineItem = new OrderLineItemEntity();
        $customizedProductTemplateLineItem->setId($templateLineItemId);
        $customizedProductTemplateLineItem->setPosition(1);
        $customizedProductTemplateLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $customizedProductTemplateLineItem->setLabel('TestTemplate');
        $customizedProductOptionLineItem = new OrderLineItemEntity();
        $customizedProductOptionLineItem->setId(Uuid::randomHex());
        $customizedProductOptionLineItem->setPosition(2);
        $customizedProductOptionLineItem->setParentId($templateLineItemId);
        $customizedProductOptionLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $customizedProductOptionLineItem->setPriceDefinition(new PercentagePriceDefinition(10));
        $customizedProductOptionLineItem->setQuantity(5);
        $customizedProductOptionLineItem->setLabel('TestOption');
        $orderLineItems = new OrderLineItemCollection(
            [
                $customizedProductTemplateLineItem,
                $customizedProductOptionLineItem,
            ]
        );
        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(
            $orderLineItems
        );
        $event = new MailBeforeValidateEvent(
            [
                'templateId' => $this->getValidMailTemplateId(),
            ],
            Context::createDefaultContext(),
            [
                'order' => $order,
            ]
        );
        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertSame(1, $customizedProductOptionLineItem->getQuantity());
    }

    public function testRestructuring(): void
    {
        $productLineItem = new OrderLineItemEntity();
        $productLineItem->setId(Uuid::randomHex());
        $productLineItem->setPosition(2);
        $productLineItem->setLabel('OrdinaryProduct');
        $productLineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $templateLineItem = new OrderLineItemEntity();
        $templateLineItem->setId(Uuid::randomHex());
        $templateLineItem->setPosition(1);
        $templateLineItem->setLabel('TestTemplate');
        $templateLineItem->setType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE);
        $optionLineItem = new OrderLineItemEntity();
        $optionLineItem->setId(Uuid::randomHex());
        $optionLineItem->setPosition(1);
        $optionLineItem->setParentId($templateLineItem->getId());
        $optionLineItem->setLabel('TestOption');
        $optionLineItem->setType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        $optionValueLineItem = new OrderLineItemEntity();
        $optionValueLineItem->setId(Uuid::randomHex());
        $optionValueLineItem->setPosition(1);
        $optionValueLineItem->setParentId($optionLineItem->getId());
        $optionValueLineItem->setLabel('TestOptionValue');
        $optionValueLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $customizedProductLineItem = new OrderLineItemEntity();
        $customizedProductLineItem->setId(Uuid::randomHex());
        $customizedProductLineItem->setPosition(2);
        $customizedProductLineItem->setLabel('CustomizedOrdinaryProduct');
        $customizedProductLineItem->setParentId($templateLineItem->getId());
        $customizedProductLineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(new OrderLineItemCollection([
            $productLineItem,
            $optionValueLineItem,
            $customizedProductLineItem,
            $optionLineItem,
            $templateLineItem,
        ]));
        $event = new MailBeforeValidateEvent(
            ['templateId' => $this->getValidMailTemplateId()],
            Context::createDefaultContext(),
            ['order' => $order]
        );
        $this->orderConfirmationSubscriber->__invoke($event);

        $lineItems = $order->getLineItems();
        static::assertNotNull($lineItems);
        static::assertSame([
            $customizedProductLineItem,
            $optionLineItem,
            $optionValueLineItem,
            $productLineItem,
        ], \array_values($lineItems->getElements()));
    }

    public function dataProviderLabel(): array
    {
        return [
            [
                ['type' => TextField::NAME, 'value' => self::TEST_VALUE],
                \sprintf('* %s: %s', self::DEFAULT_LABEL, self::TEST_VALUE),
            ],
            [
                ['type' => Textarea::NAME, 'value' => self::TEST_VALUE],
                \sprintf('* %s: %s', self::DEFAULT_LABEL, self::TEST_VALUE),
            ],
            [
                ['type' => Textarea::NAME, 'value' => \str_repeat(self::TEST_VALUE, 100)],
                \sprintf('* %s: %s[...]', self::DEFAULT_LABEL, \substr(\str_repeat(self::TEST_VALUE, 100), 0, 45)),
            ],
            [
                ['type' => DateTime::NAME, 'value' => '2015-03-26T10:58:51'],
                \sprintf('* %s: %s', self::DEFAULT_LABEL, '26.03.2015'),
            ],
            [
                ['type' => Timestamp::NAME, 'value' => '2015-03-26T10:58:51'],
                \sprintf('* %s: %s', self::DEFAULT_LABEL, '10:58'),
            ],
            [
                ['type' => FileUpload::NAME, 'media' => [['filename' => 'A.pdf'], ['filename' => 'B.pdf']]],
                \sprintf('* %s: %s', self::DEFAULT_LABEL, 'A.pdf, B.pdf'),
            ],
            [
                ['type' => ImageUpload::NAME, 'media' => [['filename' => 'A.jpg'], ['filename' => 'B.jpg']]],
                \sprintf('* %s: %s', self::DEFAULT_LABEL, 'A.jpg, B.jpg'),
            ],
            [
                ['type' => Checkbox::NAME, 'value' => 'on'],
                \sprintf('* %s', self::DEFAULT_LABEL),
            ],
            [
                [],
                \sprintf('* %s', self::DEFAULT_LABEL),
            ],
        ];
    }

    /**
     * @dataProvider dataProviderLabel
     */
    public function testValueAddedToLabel(array $payload, string $expectedLabel): void
    {
        $templateLineItem = new OrderLineItemEntity();
        $templateLineItem->setId(Uuid::randomHex());
        $templateLineItem->setPosition(1);
        $templateLineItem->setLabel('TestTemplate');
        $templateLineItem->setType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE);
        $optionLineItem = new OrderLineItemEntity();
        $optionLineItem->setId(Uuid::randomHex());
        $optionLineItem->setPosition(1);
        $optionLineItem->setParentId($templateLineItem->getId());
        $optionLineItem->setLabel(self::DEFAULT_LABEL);
        $optionLineItem->setPayload($payload);
        $optionLineItem->setType(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE);
        $customizedProductLineItem = new OrderLineItemEntity();
        $customizedProductLineItem->setId(Uuid::randomHex());
        $customizedProductLineItem->setPosition(2);
        $customizedProductLineItem->setLabel('CustomizedOrdinaryProduct');
        $customizedProductLineItem->setParentId($templateLineItem->getId());
        $customizedProductLineItem->setType(LineItem::PRODUCT_LINE_ITEM_TYPE);
        $order = new OrderEntity();
        $order->setId(Uuid::randomHex());
        $order->setLineItems(new OrderLineItemCollection([
            $customizedProductLineItem,
            $optionLineItem,
            $templateLineItem,
        ]));
        $event = new MailBeforeValidateEvent(
            ['templateId' => $this->getValidMailTemplateId()],
            Context::createDefaultContext(),
            ['order' => $order]
        );
        $this->orderConfirmationSubscriber->__invoke($event);

        static::assertSame($expectedLabel, $optionLineItem->getLabel());
    }

    private function getNestedStructure(): array
    {
        $templateLineItemId = Uuid::randomHex();
        $templateLineItem = new OrderLineItemEntity();
        $templateLineItem->setId($templateLineItemId);
        $templateLineItem->setPosition(1);
        $templateLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_TYPE
        );
        $templateLineItem->setLabel('Template');

        $productLineItemId = Uuid::randomHex();
        $productLineItem = new OrderLineItemEntity();
        $productLineItem->setId($productLineItemId);
        $productLineItem->setPosition(1);
        $productLineItem->setParentId($templateLineItemId);
        $productLineItem->setType(
            LineItem::PRODUCT_LINE_ITEM_TYPE
        );
        $productLineItem->setLabel('Product');

        $optionLineItemId = Uuid::randomHex();
        $optionLineItem = new OrderLineItemEntity();
        $optionLineItem->setId($optionLineItemId);
        $optionLineItem->setPosition(2);
        $optionLineItem->setParentId($templateLineItemId);
        $optionLineItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        $optionLineItem->setPriceDefinition(new AbsolutePriceDefinition(19.99));
        $optionLineItem->setQuantity(5);
        $optionLineItem->setLabel('Option');

        $valueItem = new OrderLineItemEntity();
        $valueItem->setId(Uuid::randomHex());
        $valueItem->setPosition(2);
        $valueItem->setParentId($optionLineItemId);
        $valueItem->setType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        $valueItem->setPriceDefinition(new AbsolutePriceDefinition(19.99));
        $valueItem->setQuantity(5);
        $valueItem->setLabel('Value');

        return [
            $templateLineItem,
            $productLineItem,
            $optionLineItem,
            $valueItem,
        ];
    }

    private function getValidMailTemplateId(
        string $mailTemplateType = MailTemplateTypes::MAILTYPE_ORDER_CONFIRM
    ): ?string {
        $mailTemplateRepository = $this->getContainer()->get('mail_template.repository');
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('mailTemplateType.technicalName', $mailTemplateType)
        );

        return $mailTemplateRepository->searchIds($criteria, Context::createDefaultContext())->firstId();
    }
}

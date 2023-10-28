<?php declare(strict_types=1);

namespace Swag\SocialShopping\Test\DataAbstractionLayer\FieldSerializer;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount as InvokedCountMatcher;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\FkFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\Test\IdsCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use SwagSocialShopping\DataAbstractionLayer\FieldSerializer\FkFieldSerializerDecorator;

class FkFieldSerializerDecoratorTest extends TestCase
{
    private IdsCollection $ids;

    /**
     * @dataProvider serializerDataProvider
     */
    public function testEncodeExtractsTypeId(
        FkField $field,
        EntityExistence $existence,
        string $actual,
        string $expected,
        InvokedCountMatcher $count
    ): void {
        $decorated = $this->createMock(FkFieldSerializer::class);
        $decorated->expects(static::once())->method('encode');

        $serializer = new FkFieldSerializerDecorator($decorated);

        $pair = $this->getKeyValuePair($actual);
        $pair->expects($count)->method('setValue')->with($expected);

        $bag = $this->createMock(WriteParameterBag::class);

        $serializer->encode($field, $existence, $pair, $bag);
    }

    public function serializerDataProvider(): \Generator
    {
        $this->ids = new IdsCollection();

        yield 'handle SOC typeId' => [
            'field' => $this->getField('typeId'),
            'existence' => $this->getExistence(SalesChannelDefinition::ENTITY_NAME),
            'actual' => $this->ids->get('type') . '-facebook',
            'expected' => $this->ids->get('type'),
            'count' => static::once(),
        ];

        yield 'handle core typeId' => [
            'field' => $this->getField('typeId'),
            'existence' => $this->getExistence(SalesChannelDefinition::ENTITY_NAME),
            'actual' => $this->ids->get('type'),
            'expected' => $this->ids->get('type'),
            'count' => static::once(),
        ];

        yield 'does nothing because wrong property' => [
            'field' => $this->getField('currencyId'),
            'existence' => $this->getExistence(SalesChannelDefinition::ENTITY_NAME),
            'actual' => $this->ids->get('type'),
            'expected' => $this->ids->get('type'),
            'count' => static::never(),
        ];

        yield 'does nothing because wrong entity' => [
            'field' => $this->getField('typeId'),
            'existence' => $this->getExistence(ProductDefinition::ENTITY_NAME),
            'actual' => $this->ids->get('type'),
            'expected' => $this->ids->get('type'),
            'count' => static::never(),
        ];
    }

    public function testDecodeCallsAlwaysDecoratedMethod(): void
    {
        $field = $this->createMock(FkField::class);
        $value = 'foo';

        $decorated = $this->createMock(FkFieldSerializer::class);
        $decorated->expects(static::once())->method('decode')->with($field, $value);

        $serializer = new FkFieldSerializerDecorator($decorated);
        $serializer->decode($field, $value);
    }

    public function testNormalizeCallsAlwaysDecoratedMethod(): void
    {
        $field = $this->createMock(FkField::class);
        $data = ['foo', 'bar'];
        $bag = $this->createMock(WriteParameterBag::class);

        $decorated = $this->createMock(FkFieldSerializer::class);
        $decorated->expects(static::once())->method('normalize')->with($field, $data, $bag);

        $serializer = new FkFieldSerializerDecorator($decorated);
        $serializer->normalize($field, $data, $bag);
    }

    /**
     * @return FkField&MockObject
     */
    private function getField(string $propertyName): FkField
    {
        $field = $this->createMock(FkField::class);
        $field->method('getPropertyName')->willReturn($propertyName);

        return $field;
    }

    /**
     * @return EntityExistence&MockObject
     */
    private function getExistence(string $entityName): EntityExistence
    {
        $existence = $this->createMock(EntityExistence::class);
        $existence->method('getEntityName')->willReturn($entityName);

        return $existence;
    }

    /**
     * @return KeyValuePair&MockObject
     */
    private function getKeyValuePair(string $value): KeyValuePair
    {
        $pair = $this->createMock(KeyValuePair::class);
        $pair->method('getValue')->willReturn($value);

        return $pair;
    }
}

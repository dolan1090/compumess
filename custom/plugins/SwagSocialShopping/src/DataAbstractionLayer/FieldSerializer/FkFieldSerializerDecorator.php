<?php declare(strict_types=1);

namespace SwagSocialShopping\DataAbstractionLayer\FieldSerializer;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\FieldSerializerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use SwagSocialShopping\Exception\UnexpectedSalesChannelTypeException;

class FkFieldSerializerDecorator implements FieldSerializerInterface
{
    protected AbstractFieldSerializer $decorated;

    public function __construct(AbstractFieldSerializer $decorated)
    {
        $this->decorated = $decorated;
    }

    public function encode(Field $field, EntityExistence $existence, KeyValuePair $data, WriteParameterBag $parameters): \Generator
    {
        if ($existence->getEntityName() === SalesChannelDefinition::ENTITY_NAME && $field->getPropertyName() === 'typeId') {
            $typeId = $this->getTypeIdWithoutNetworkSuffix($data->getValue());
            $data->setValue($typeId);
        }

        return $this->decorated->encode($field, $existence, $data, $parameters);
    }

    public function decode(Field $field, $value): ?string
    {
        return $this->decorated->decode($field, $value);
    }

    public function normalize(Field $field, array $data, WriteParameterBag $parameters): array
    {
        return $this->decorated->normalize($field, $data, $parameters);
    }

    private function getTypeIdWithoutNetworkSuffix(string $typeId): string
    {
        $result = \preg_replace('/-\w+/', '', $typeId);

        if (!$result) {
            throw new UnexpectedSalesChannelTypeException($typeId, \preg_last_error());
        }

        return $result;
    }
}

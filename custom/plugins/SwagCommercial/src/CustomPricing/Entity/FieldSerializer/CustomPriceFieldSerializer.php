<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Entity\FieldSerializer;

use Shopware\Commercial\CustomPricing\Entity\CustomPrice\Price\CustomPrice;
use Shopware\Commercial\CustomPricing\Entity\CustomPrice\Price\CustomPriceCollection;
use Shopware\Commercial\CustomPricing\Entity\Field\CustomPriceField;
use Shopware\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\AbstractFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\PriceFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection as DalPriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
#[Package('inventory')]
class CustomPriceFieldSerializer extends AbstractFieldSerializer
{
    /**
     * @internal
     */
    public function __construct(
        DefinitionInstanceRegistry $definitionRegistry,
        ValidatorInterface $validator,
        private readonly PriceFieldSerializer $priceFieldSerializer
    ) {
        parent::__construct($validator, $definitionRegistry);
    }

    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof CustomPriceField) {
            throw DataAbstractionLayerException::invalidSerializerField(CustomPriceField::class, $field);
        }

        /** @var list<array{quantityStart?: int|string, quantityEnd?: int|string, price: array<string, mixed>, extensions?: mixed}>|null $value */
        $value = $data->getValue();

        if ($this->requiresValidation($field, $existence, $value, $parameters)) {
            if ($value !== null) {
                foreach ($value as &$row) {
                    unset($row['extensions']);
                }
            }
            $data->setValue($value);

            if ($field->is(Required::class)) {
                $this->validate([new NotBlank()], $data, $parameters->getPath());
            }

            $constraints = $this->getCachedConstraints($field);
            $pricePath = $parameters->getPath() . '/price';

            foreach ($value ?? [] as $index => $price) {
                $this->validate($constraints, new KeyValuePair((string) $index, $price, true), $pricePath);
            }

            $result = [];
            $priceField = new PriceField('price', 'price');

            foreach ($value ?? [] as $customPrices) {
                $quantityStart = !empty($customPrices['quantityStart']) ? (int) $customPrices['quantityStart'] : null;
                $quantityEnd = !empty($customPrices['quantityEnd']) ? (int) $customPrices['quantityEnd'] : null;

                $items = $this->priceFieldSerializer->encode(
                    $priceField,
                    $existence,
                    new KeyValuePair('', $customPrices['price'], true),
                    $parameters
                );

                foreach ($items as $item) {
                    /** @var ?string $item */
                    $result[] = [
                        'quantityStart' => $quantityStart,
                        'quantityEnd' => $quantityEnd,
                        'price' => $item ? json_decode((string) $item, null, 512, \JSON_THROW_ON_ERROR) : null,
                    ];
                }
            }
            $value = $result;
        }

        if ($value !== null) {
            $value = (string) \json_encode($value, \JSON_UNESCAPED_UNICODE | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR | \JSON_INVALID_UTF8_IGNORE);
        }

        yield $field->getStorageName() => $value;
    }

    /**
     * @return array<CustomPriceCollection>|null
     */
    public function decode(Field $field, mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (\is_string($value)) {
            $value = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
        }

        $collections = [];

        /** @var array<int,
         *      array{
         *          quantityStart?: int|null,
         *          quantityEnd?: int|null,
         *          price?: array<int, array{currencyId: string, gross: float, net: float, linked: boolean, percentage?: array{gross: float, net: float},listPrice?: array{gross?: float, net: float, linked: boolean}, regulationPrice?: array{gross?: float, net: float, linked: boolean}}>
         *      }> $value
         */
        foreach ($value as $row) {
            $quantityStart = $row['quantityStart'] ?? 1;
            $quantityEnd = $row['quantityEnd'] ?? null;
            $customPrices = $row['price'] ?? null;

            $collection = $this->priceFieldSerializer->decode($field, $customPrices);
            if (!$collection instanceof DalPriceCollection) {
                continue;
            }

            $customCollection = new CustomPriceCollection();
            foreach ($collection as $price) {
                $convertedPrice = CustomPrice::createFrom($price);
                $convertedPrice->setQuantityStart($quantityStart);
                $convertedPrice->setQuantityEnd($quantityEnd);

                $customCollection->add($convertedPrice);
            }
            $collections[] = $customCollection;
        }

        return $collections;
    }

    protected function getConstraints(Field $field): array
    {
        return [
            new Collection([
                'allowExtraFields' => true,
                'allowMissingFields' => false,
                'fields' => [
                    'quantityStart' => [new Optional(), new Type('numeric')],
                    'quantityEnd' => [new Optional(), new Type('numeric')],
                    'price' => [new Type('array')],
                ],
            ]),
        ];
    }
}

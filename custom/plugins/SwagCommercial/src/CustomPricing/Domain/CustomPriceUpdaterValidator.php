<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Domain;

use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

/**
 * @internal
 *
 * @phpstan-import-type  CustomPriceUploadType from CustomPriceUpdater
 * @phpstan-import-type  CustomPriceDeleteType from CustomPriceUpdater
 */
#[Package('inventory')]
class CustomPriceUpdaterValidator
{
    /**
     * @param CustomPriceUploadType $price
     *
     * @return array<int, array{entities: array{}, errors: array<int, non-empty-string>}|non-empty-string>|null
     */
    public function validateUpsert(array $price): ?array
    {
        $results = [];
        $validator = Validation::createValidator();

        if (!(isset($price['customerGroupId']) xor isset($price['customerId']))) {
            $results[] = \sprintf('%s: Must have either `customerId` or `customerGroupId` defined', '[customerId]');

            return $results;
        }

        $constraint = new Assert\Collection([
            'productId' => [new Assert\Type('string'), new Assert\NotNull()],
            'customerId' => new Assert\Optional([new Assert\Type(['type' => ['string', 'null']])]),
            'customerGroupId' => new Assert\Optional([new Assert\Type(['type' => ['string', 'null']])]),
            'price' => new Assert\All([
                new Assert\Collection([
                    'quantityStart' => [new Assert\Type('integer'), new Assert\NotNull()],
                    'quantityEnd' => new Assert\Type(['integer', 'null']),
                    'price' => new Assert\All([
                        new Assert\Collection([
                            'currencyId' => [new Assert\Type('string'), new Assert\NotNull()],
                            'gross' => [new Assert\Type(['numeric']), new Assert\NotNull()],
                            'net' => [new Assert\Type(['numeric']), new Assert\NotNull()],
                            'linked' => [new Assert\Type('bool'), new Assert\NotNull()],
                            'listPrice' => new Assert\Optional(
                                new Assert\Collection([
                                    'gross' => [new Assert\Type(['numeric']), new Assert\NotNull()],
                                    'net' => [new Assert\Type(['numeric']), new Assert\NotNull()],
                                    'linked' => [new Assert\Type('bool'), new Assert\NotNull()],
                                ])
                            ),
                            'regulationPrice' => new Assert\Optional(
                                new Assert\Collection([
                                    'gross' => [new Assert\Type(['numeric']), new Assert\NotNull()],
                                    'net' => [new Assert\Type(['numeric']), new Assert\NotNull()],
                                    'linked' => [new Assert\Type('bool'), new Assert\NotNull()],
                                ])
                            ),
                        ]),
                    ]),
                ]),
            ]),
        ]);

        return $this->convertViolationListToResponseArray(
            $validator->validate($price, $constraint)
        );
    }

    /**
     * @param CustomPriceDeleteType $deleteOperation
     *
     * @return array<int, non-empty-string>|null
     */
    public function validateDelete(array $deleteOperation): ?array
    {
        $results = [];
        if (empty($deleteOperation['productIds']) && empty($deleteOperation['customerIds']) && empty($deleteOperation['customerGroupIds'])) {
            $results[] = 'At least one of the following parameters must be provided: productIds, customerIds, customerGroupIds';

            return $results;
        }
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(
            [
                'productIds' => [
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Uuid(null, null, null, false),
                    ]),
                ],
                'customerIds' => [
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Uuid(null, null, null, false),
                    ]),
                ],
                'customerGroupIds' => [
                    new Assert\Type('array'),
                    new Assert\All([
                        new Assert\Uuid(null, null, null, false),
                    ]),
                ],
            ],
            null,
            null,
            null,
            true
        );

        return $this->convertViolationListToResponseArray(
            $validator->validate($deleteOperation, $constraint)
        );
    }

    /**
     * @return array<int, non-empty-string>|null
     */
    private function convertViolationListToResponseArray(ConstraintViolationListInterface $violationList): ?array
    {
        if ($violationList->count() === 0) {
            return null;
        }
        $singleItemResults = [];
        foreach ($violationList as $violation) {
            $singleItemResults[] = \sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
        }

        return $singleItemResults;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Exception\Domain;

use Shopware\Commercial\CustomPricing\Domain\CustomPriceUpdater;
use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('inventory')]
class CustomPriceException extends HttpException
{
    final public const MISSING_INSTRUCTIONS = 'CUSTOM_PRICING__MISSING_INSTRUCTIONS';
    final public const INVALID_SYNC_ACTION = 'CUSTOM_PRICING__INVALID_SYNC_ACTION';

    /**
     * @param array<string, mixed> $operation
     */
    public static function incorrectOperationKeys(int $index, array $operation): CustomPriceException
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MISSING_INSTRUCTIONS,
            \sprintf(
                'Incorrect operation keys provided for operation with index %s. Got \'%s\', expected \'%s\'',
                $index,
                implode(', ', array_keys($operation)),
                implode(', ', CustomPriceUpdater::OPERATION_KEYS)
            )
        );
    }

    public static function invalidAction(string $action): CustomPriceException
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_SYNC_ACTION,
            \sprintf(
                'Action \'%s\' given in the operation record is not recognised, expected \'%s\'',
                $action,
                implode(', ', CustomPriceUpdater::PERMITTED_ACTIONS),
            )
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning;

use Shopware\Commercial\ReturnManagement\Domain\Returning\Exception\InvalidReturnItemStatesException;
use Shopware\Commercial\ReturnManagement\Domain\Returning\Exception\OrderReturnAggregationException;
use Shopware\Commercial\ReturnManagement\Domain\Returning\Exception\OrderReturnCreateException;
use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class OrderReturnException extends HttpException
{
    public const CANNOT_CREATE_RETURN_CODE = 'CANNOT_CREATE_RETURN';
    public const INVALID_RETURN_ITEM_STATES_CODE = 'INVALID_RETURN_ITEM_STATES';
    public const CANNOT_AGGREGATION_ORDER_RETURN_CODE = 'CANNOT_AGGREGATION_ORDER_RETURN';
    public const ORDER_RETURN_NOT_FOUND = 'ORDER_RETURN_NOT_FOUND';

    public static function cannotCreateReturn(?\Throwable $e = null): OrderReturnException
    {
        return new OrderReturnCreateException(Response::HTTP_BAD_REQUEST, self::CANNOT_CREATE_RETURN_CODE, 'Cannot create order return', [], $e);
    }

    public static function invalidReturnItemStates(?\Throwable $e = null): OrderReturnException
    {
        return new InvalidReturnItemStatesException(Response::HTTP_BAD_REQUEST, self::INVALID_RETURN_ITEM_STATES_CODE, 'Invalid return item states', [], $e);
    }

    public static function cannotAggregateOrderReturn(?\Throwable $e = null): OrderReturnException
    {
        return new OrderReturnAggregationException(Response::HTTP_BAD_REQUEST, self::CANNOT_AGGREGATION_ORDER_RETURN_CODE, 'Cannot aggregate order return', [], $e);
    }

    public static function orderReturnNotFound(string $returnId, ?\Throwable $e = null): OrderReturnException
    {
        return new OrderReturnException(Response::HTTP_NOT_FOUND, self::ORDER_RETURN_NOT_FOUND, 'Order return with id {{ returnId }} not found', ['returnId' => $returnId], $e);
    }
}

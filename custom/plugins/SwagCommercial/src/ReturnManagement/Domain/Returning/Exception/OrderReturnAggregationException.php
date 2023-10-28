<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Returning\Exception;

use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnException;
use Shopware\Core\Framework\Log\Package;

/**
 * @final
 */
#[Package('checkout')]
class OrderReturnAggregationException extends OrderReturnException
{
}

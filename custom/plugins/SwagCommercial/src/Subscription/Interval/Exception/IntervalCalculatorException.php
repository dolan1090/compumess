<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Interval\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class IntervalCalculatorException extends HttpException
{
    public const CRON_IMPOSSIBLE_EXPRESSION = 'INTERVAL_CALCULATOR__CRON_IMPOSSIBLE_EXPRESSION';

    public static function cronImpossibleExpression(string $expression): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::CRON_IMPOSSIBLE_EXPRESSION,
            \sprintf('CRON "%s" is impossible to calculate.', $expression)
        );
    }
}

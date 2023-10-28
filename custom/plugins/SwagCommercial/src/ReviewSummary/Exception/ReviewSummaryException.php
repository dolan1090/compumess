<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @final
 *
 * @internal
 */
#[Package('system-settings')]
class ReviewSummaryException extends HttpException
{
    final public const INVALID_ARGUMENT = 'REVIEW_SUMMARY__INVALID_ARGUMENT';

    final public const NO_REVIEWS_FOUND = 'REVIEW_SUMMARY__NO_REVIEWS_FOUND';

    public static function invalidArgumentException(string $message = 'Invalid Argument'): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_ARGUMENT,
            $message
        );
    }

    public static function noReviewsFound(string $productId, string $message = 'No reviews found for'): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::NO_REVIEWS_FOUND,
            $message . ' ' . $productId
        );
    }
}

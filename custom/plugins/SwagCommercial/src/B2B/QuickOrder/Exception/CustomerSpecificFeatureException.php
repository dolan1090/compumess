<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSpecificFeatureException extends HttpException
{
    public const FEATURE_NOT_ALLOWED = 'CUSTOMER_SPECIFIC_FEATURES__FEATURE_NOT_ALLOWED';

    public static function notAllowed(string $feature): self
    {
        return new self(
            Response::HTTP_FORBIDDEN,
            self::FEATURE_NOT_ALLOWED,
            sprintf('You are not allowed to access feature %s.', $feature)
        );
    }
}

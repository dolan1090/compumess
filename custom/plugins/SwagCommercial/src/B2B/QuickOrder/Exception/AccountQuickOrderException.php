<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Exception;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('checkout')]
class AccountQuickOrderException extends HttpException
{
    public const MISSING_FILE = 'QUICK_ORDER__MISSING_CSV_FILE';
    public const INVALID_FILE = 'QUICK_ORDER__INVALID_CSV_FILE';
    public const OVER_SIZE_ERROR = 'QUICK_ORDER__OVER_SIZE_ERROR';
    public const INVALID_FILE_EXTENSION = 'QUICK_ORDER__INVALID_CSV_FILE';
    public const FILE_NOT_FOUND = 'QUICK_ORDER__FILE_NOT_FOUND';

    public static function missingFile(): AccountQuickOrderException
    {
        return new self(Response::HTTP_BAD_REQUEST, self::MISSING_FILE, 'The CSV file is missing.');
    }

    public static function invalidFile(): AccountQuickOrderException
    {
        return new self(Response::HTTP_BAD_REQUEST, self::INVALID_FILE, 'The CSV file is invalid.');
    }

    public static function overSizeError(): AccountQuickOrderException
    {
        return new self(Response::HTTP_BAD_REQUEST, self::OVER_SIZE_ERROR, 'The CSV file cannot be over 100MB.');
    }

    public static function fileCannotBeFound(string $pathName): AccountQuickOrderException
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::FILE_NOT_FOUND,
            sprintf('The CSV file (%s) cannot be found.', $pathName)
        );
    }

    public static function extensionError(string $extension): AccountQuickOrderException
    {
        return new self(Response::HTTP_BAD_REQUEST, self::INVALID_FILE_EXTENSION, sprintf('The extension %s is not allowed.', $extension));
    }

    public static function licenseExpired(): LicenseExpiredException
    {
        return new LicenseExpiredException();
    }
}

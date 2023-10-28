<?php declare(strict_types=1);

namespace Shopware\Commercial\Captcha\Exception;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
class FriendlyCaptchaException extends HttpException
{
    public static function licenseExpired(): LicenseExpiredException
    {
        return new LicenseExpiredException();
    }
}

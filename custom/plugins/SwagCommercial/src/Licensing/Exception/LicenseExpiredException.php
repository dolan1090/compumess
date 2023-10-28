<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Exception;

use Shopware\Core\Framework\Log\Package;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
class LicenseExpiredException extends \RuntimeException
{
}

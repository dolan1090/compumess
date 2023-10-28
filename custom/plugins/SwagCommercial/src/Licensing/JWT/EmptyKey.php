<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\JWT;

use Lcobucci\JWT\Signer\Key;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('merchant-services')]
final class EmptyKey implements Key
{
    private function __construct()
    {
    }

    public static function create(): self
    {
        return new self();
    }

    public function contents(): string
    {
        return '';
    }

    public function passphrase(): string
    {
        return '';
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use Doctrine\DBAL\Exception\ConnectionException;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha512;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\JWT\EmptyKey;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('merchant-services')]
final class License
{
    public const CONFIG_STORE_LICENSE_KEY = 'core.store.licenseKey';
    public const CONFIG_STORE_LICENSE_HOST = 'core.store.licenseHost';

    private static false|UnencryptedToken $license = false;

    private static Features $features;

    /**
     * @var array<string, string|int|boolean>
     */
    private static array $toggles;

    public static function set(SystemConfigService $config, Features $features): void
    {
        try {
            $licenseKey = $config->getString(self::CONFIG_STORE_LICENSE_KEY);

            $licenseHost = $config->getString(self::CONFIG_STORE_LICENSE_HOST);
        } catch (ConnectionException) {
            self::$license = false;

            return;
        }

        try {
            $jwt = Configuration::forAsymmetricSigner(
                new Sha512(),
                EmptyKey::create(),
                InMemory::file(__DIR__ . '/public.pem')
            );

            /** @var UnencryptedToken $token */
            $token = $jwt->parser()->parse($licenseKey);
        } catch (\Throwable) {
            self::$license = false;

            return;
        }

        try {
            $jwt->validator()->assert(
                $token,
                new StrictValidAt(SystemClock::fromUTC())
            );
        } catch (RequiredConstraintsViolated) {
            // Delete expired token, so we throw the exception only once
            $config->delete(self::CONFIG_STORE_LICENSE_KEY);

            throw new LicenseExpiredException();
        }

        $jwt->validator()->assert(
            $token,
            new SignedWith($jwt->signer(), $jwt->verificationKey()),
            new PermittedFor($licenseHost),
        );

        self::$features = $features;
        self::$license = $token;
        self::$toggles = $features->getAllEnabledToggles($token);
    }

    public static function hasLicense(): bool
    {
        return self::$license !== false;
    }

    public static function get(string $toggle): string|bool|int
    {
        if (self::$license === false) {
            return false;
        }

        return self::$toggles[$toggle] ?? false;
    }

    /**
     * @return array<string, string|int|boolean>
     */
    public static function all(): array
    {
        if (self::$license === false) {
            return [];
        }

        return self::$toggles;
    }

    /**
     * @return array<Feature>
     */
    public static function availableFeatures(): array
    {
        if (self::$license === false) {
            return [];
        }

        return self::$features->getAllAvailable(self::$license);
    }
}

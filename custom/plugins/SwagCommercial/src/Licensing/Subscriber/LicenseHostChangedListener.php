<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Licensing\LicenseUpdater;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\StoreRequestOptionsProvider;
use Shopware\Core\System\SystemConfig\Event\SystemConfigChangedEvent;
use Shopware\Core\System\SystemConfig\Event\SystemConfigDomainLoadedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('merchant-services')]
class LicenseHostChangedListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $configService,
        private readonly LicenseUpdater $licenseUpdater
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SystemConfigChangedEvent::class => 'syncLicenseKey',
            SystemConfigDomainLoadedEvent::class => 'removeLicenseKeyFromDomain',
        ];
    }

    public function syncLicenseKey(SystemConfigChangedEvent $event): void
    {
        if ($event->getKey() === StoreRequestOptionsProvider::CONFIG_KEY_STORE_LICENSE_DOMAIN) {
            // remove old license key, as it won't match the new license host anymore
            $this->configService->delete(License::CONFIG_STORE_LICENSE_KEY);
        }

        if ($event->getKey() === StoreRequestOptionsProvider::CONFIG_KEY_STORE_SHOP_SECRET && $event->getValue() !== null) {
            try {
                // try to fetch a new license key after account re-authentication
                $this->licenseUpdater->sync();
            } catch (\Throwable) {
                // ignore any errors, as there probably is no license key for the account
            }
        }
    }

    /**
     * We have to remove the license key from the system config domain,
     * otherwise it is exposed in the admin and the admin will overwrite it automatically,
     * thus circumventing our reset logic on license host change.
     */
    public function removeLicenseKeyFromDomain(SystemConfigDomainLoadedEvent $event): void
    {
        if ($event->getDomain() !== 'core.store.') {
            return;
        }

        $config = $event->getConfig();
        unset($config[License::CONFIG_STORE_LICENSE_KEY]);

        $event->setConfig($config);
    }
}

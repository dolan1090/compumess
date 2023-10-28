<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('merchant-services')]
final class LicenseReporter
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Client $client,
        private readonly AbstractStoreRequestOptionsProvider $optionsProvider,
        private readonly SystemConfigService $configService,
        private readonly string $rootDir
    ) {
    }

    public function report(): void
    {
        $context = Context::createDefaultContext();
        $headers = $this->optionsProvider->getAuthenticationHeader($context);

        if (isset($headers['X-Shopware-Platform-Token'])) {
            unset($headers['X-Shopware-Platform-Token']);
        }

        $toggles = [];

        foreach (array_keys(License::all()) as $toggle) {
            $toggles[$toggle] = License::get($toggle);
        }

        $this->client->post('/swplatform/commerciallicensekeyviolations', [
            RequestOptions::HEADERS => $headers,
            RequestOptions::QUERY => $this->optionsProvider->getDefaultQueryParameters($context),
            RequestOptions::JSON => [
                'licenseKey' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
                'licenseToggles' => $toggles,
            ],
        ]);
    }

    public function reportNow(string $reason): void
    {
        $context = Context::createDefaultContext();
        $headers = $this->optionsProvider->getAuthenticationHeader($context);

        if (isset($headers['X-Shopware-Platform-Token'])) {
            unset($headers['X-Shopware-Platform-Token']);
        }

        $this->client->post('/swplatform/commerciallicensekeytoggle', [
            RequestOptions::HEADERS => $headers,
            RequestOptions::QUERY => $this->optionsProvider->getDefaultQueryParameters($context),
            RequestOptions::JSON => [
                'licenseToggle' => $reason,
                'class' => $this->getLicenseFilePath(),
            ],
        ]);
    }

    private function getLicenseFilePath(): string
    {
        /** @var string $fileName */
        $fileName = (new \ReflectionClass(License::class))->getFileName();

        if ($this->rootDir === '' || !str_starts_with($fileName, $this->rootDir)) {
            return $fileName;
        }

        return ltrim(substr($fileName, \strlen($this->rootDir)), '/');
    }
}

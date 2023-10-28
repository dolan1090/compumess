<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * @internal
 */
#[Package('merchant-services')]
class LicenseUpdater
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Client $client,
        private readonly AbstractStoreRequestOptionsProvider $optionsProvider,
        private readonly SystemConfigService $configService
    ) {
    }

    public function sync(): void
    {
        $key = $this->fetchLicenseInformation();

        if ($key === null) {
            return;
        }
        $this->configService->set(License::CONFIG_STORE_LICENSE_KEY, $key);
    }

    private function fetchLicenseInformation(): ?string
    {
        $context = Context::createDefaultContext();
        $headers = $this->optionsProvider->getAuthenticationHeader($context);

        if (isset($headers['X-Shopware-Platform-Token'])) {
            unset($headers['X-Shopware-Platform-Token']);
        }

        try {
            $response = $this->client->get('/swplatform/commerciallicensekey', [
                RequestOptions::HEADERS => $headers,
                RequestOptions::QUERY => $this->optionsProvider->getDefaultQueryParameters($context),
            ]);
        } catch (RequestException $e) {
            if (!$e->getResponse()) {
                throw $e;
            }

            try {
                $content = \json_decode($e->getResponse()->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
                if (!\is_array($content)) {
                    throw $e;
                }

                // sbp does not know the shop and throws and 403 exception. This causes problems in our test environment when devs want to deploy a shop
                if (isset($content['code']) && $content['code'] === 'ShopwarePlatformException-16') {
                    return null;
                }
            } catch (\Throwable) {
                throw $e;
            }

            throw $e;
        }

        /** @var array{'key': string} $json */
        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        return $json['key'];
    }
}

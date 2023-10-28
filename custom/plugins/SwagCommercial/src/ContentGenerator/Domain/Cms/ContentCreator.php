<?php declare(strict_types=1);

namespace Shopware\Commercial\ContentGenerator\Domain\Cms;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\ContentGenerator\Exception\ContentGeneratorException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @final
 *
 * @internal
 */
#[Package('content')]
class ContentCreator
{
    private const AI_PROXY_ENDPOINT = 'https://ai-services.apps.shopware.io/api/cms-content/';

    /**
     * @internal
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly SystemConfigService $configService,
    ) {
    }

    public function generate(string $sentence): string
    {
        if (!License::get('CONTENT_GENERATOR-9573958')) {
            throw new LicenseExpiredException();
        }

        try {
            $res = $this->client->request(Request::METHOD_POST, self::AI_PROXY_ENDPOINT . 'generate', [
                'json' => [
                    'sentence' => $sentence,
                ],
                'headers' => [
                    'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
                ],
            ]);

            /** @var array{content: string} $response */
            $response = json_decode($res->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (GuzzleException|\JsonException) {
            throw ContentGeneratorException::contentGenerateError();
        }

        return $response['content'];
    }

    public function edit(string $input, ?string $instruction): string
    {
        if (!License::get('CONTENT_GENERATOR-9573958')) {
            throw new LicenseExpiredException();
        }

        try {
            $res = $this->client->request(Request::METHOD_POST, self::AI_PROXY_ENDPOINT . 'edit', [
                'json' => [
                    'input' => $input,
                    'instruction' => $instruction,
                ],
                'headers' => [
                    'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
                ],
            ]);

            /** @var array{content: string} $response */
            $response = json_decode($res->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (GuzzleException|\JsonException) {
            throw ContentGeneratorException::contentEditError();
        }

        return $response['content'];
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\TextGenerator\Domain\Product;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Term\TokenizerInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @final
 *
 * @internal
 */
#[Package('inventory')]
class DescriptionGenerator
{
    private const AI_PRODUCT_DESCRIPTION_ENDPOINT = 'https://ai-services.apps.shopware.io/api/product-description/generate';

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ClientInterface $client,
        private readonly SystemConfigService $configService,
        private readonly TokenizerInterface $tokenizer
    ) {
    }

    /**
     * @param array<string, string> $generationContext
     *
     * @throws GuzzleException
     */
    public function generate(array $generationContext): string
    {
        if (!License::get('TEXT_GENERATOR-2946372')) {
            throw new LicenseExpiredException();
        }

        if (!isset($generationContext['title'])) {
            throw new \InvalidArgumentException('Missing required parameter title.');
        }

        if (isset($generationContext['languageId'])) {
            $generationContext['locale'] = $this->getLocale($generationContext['languageId']);

            unset($generationContext['languageId']);
        }

        if (isset($generationContext['keywords'])) {
            $generationContext['keywords'] = $this->tokenizer->tokenize($generationContext['keywords']);
        }

        $response = $this->client->request(Request::METHOD_POST, self::AI_PRODUCT_DESCRIPTION_ENDPOINT, [
            'json' => $generationContext,
            'headers' => [
                'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        /** @var array{description: string} $response */
        $response = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        return $response['description'];
    }

    private function getLocale(string $languageId): string
    {
        /** @var string|null $locale */
        $locale = $this->connection->fetchOne(
            'SELECT locale.code FROM locale INNER JOIN language ON locale.id = language.locale_id  WHERE language.id = :languageId',
            ['languageId' => Uuid::fromHexToBytes($languageId)]
        );

        if ($locale === null) {
            throw new \InvalidArgumentException(sprintf('Could not find locale for languageId "%s"', $languageId));
        }

        return $locale;
    }
}

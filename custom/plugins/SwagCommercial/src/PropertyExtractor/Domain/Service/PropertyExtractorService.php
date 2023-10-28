<?php declare(strict_types=1);

namespace Shopware\Commercial\PropertyExtractor\Domain\Service;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\PropertyExtractor\Domain\Exception\PropertyExtractorServiceUnavailableException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Package('business-ops')]
class PropertyExtractorService
{
    private const AI_PROPERTY_EXTRACTION_ENDPOINT = 'https://ai-services.apps.shopware.io/api/property-extractor/generate';

    /**
     * @internal
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Connection $connection,
        private readonly SystemConfigService $configService
    ) {
    }

    /**
     * @return array<string, string[]>
     */
    public function getProperties(string $description, Context $context): array
    {
        if (!License::get('PROPERTY_EXTRACTOR-4927340')) {
            throw new LicenseExpiredException();
        }

        $description = $this->sanitizeDescription($description);

        if (\strlen($description) < 200) {
            throw new \InvalidArgumentException('Description must at least be 200 characters long');
        }

        $response = $this->client->request(Request::METHOD_POST, self::AI_PROPERTY_EXTRACTION_ENDPOINT, [
            'json' => [
                'description' => $description,
                'locale' => $this->getLocale($context->getLanguageId()),
            ],
            'headers' => [
                'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            throw new PropertyExtractorServiceUnavailableException();
        }

        try {
            $contents = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Invalid JSON returned');
        }

        $contents = \is_array($contents) ? $contents : [];

        /** @var array<string, string[]> $properties */
        $properties = $contents['properties'] ?? [];

        return $this->cleanupProperties($properties);
    }

    /**
     * Reduce the length to 10.5k characters to keep the prompt under the limit of 4k tokens
     */
    private function sanitizeDescription(string $description): string
    {
        return substr(
            preg_replace('/\s+/', ' ', strip_tags($description)) ?: '',
            0,
            10500
        );
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

    /**
     * @param array<string, string[]> $properties
     *
     * @return array<string, string[]>
     */
    private function cleanupProperties(array $properties): array
    {
        $cleanedProperties = [];

        foreach ($properties as $name => $values) {
            if (!\is_string($name) || \strlen($name) > 40) {
                continue;
            }

            $values = array_filter($values, fn (string $value) => \strlen($value) <= 40);

            if (empty($values)) {
                continue;
            }

            $cleanedProperties[$name] = $values;
        }

        return $cleanedProperties;
    }
}

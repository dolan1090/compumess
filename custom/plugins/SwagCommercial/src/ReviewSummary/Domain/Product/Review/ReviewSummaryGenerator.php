<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Domain\Product\Review;

use GuzzleHttp\ClientInterface;
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
#[Package('inventory')]
class ReviewSummaryGenerator
{
    private const AI_REVIEW_SUMMARY_ENDPOINT = 'https://ai-services.apps.shopware.io/api/review-summary/generate';

    /**
     * @internal
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly SystemConfigService $configService
    ) {
    }

    /**
     * @return array<mixed, mixed>
     */
    public function generate(GenerationContext $generationContext): array
    {
        if (!License::get('REVIEW_SUMMARY-8147095')) {
            throw new LicenseExpiredException();
        }

        $response = $this->client->request(Request::METHOD_POST, self::AI_REVIEW_SUMMARY_ENDPOINT, [
            'json' => $generationContext->jsonSerialize(),
            'headers' => [
                'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        $responseContent = $response->getBody()->getContents();

        if ($responseContent === '') {
            return [];
        }

        try {
            /** @var array{description: string} $responseArray */
            $responseArray = json_decode($responseContent, true, 512, \JSON_THROW_ON_ERROR|\JSON_OBJECT_AS_ARRAY);
        } catch (\Throwable $e) {
            $responseArray = [
                'review-summaries' => [],
            ];
        }

        if (!isset($responseArray['review-summaries'])) {
            return [];
        }

        return $this->remapLanguageIds($responseArray['review-summaries'], $generationContext);
    }

    /**
     * @param array<mixed, mixed> $response
     *
     * @return array<mixed, mixed|null>
     */
    public function remapLanguageIds(array $response, GenerationContext $generationContext): array
    {
        if (empty($generationContext->languageIds)) {
            return $response;
        }
        $responseWithIds = [];
        foreach ($response as $locale => $summary) {
            if (isset($generationContext->languageIds[$locale])) {
                $responseWithIds[$generationContext->languageIds[$locale]] = $summary;
            }
        }

        return $responseWithIds;
    }
}

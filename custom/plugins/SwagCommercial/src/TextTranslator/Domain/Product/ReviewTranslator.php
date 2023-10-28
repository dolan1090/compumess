<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Domain\Product;

use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\TextTranslator\Exception\ReviewTranslatorException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @phpstan-type Review array{id: string, title: ?string, content: ?string, comment: ?string, language_name: string}
 */
#[Package('inventory')]
class ReviewTranslator
{
    public const AI_PRODUCT_REVIEW_TRANSLATION_ENDPOINT = 'https://ai-services.apps.shopware.io/api/product-review/translate';

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
     * @throws GuzzleException
     *
     * @return array<string, mixed>
     */
    public function translate(string $reviewId, string $languageId): array
    {
        if (!License::get('REVIEW_TRANSLATOR-1649854')) {
            throw new LicenseExpiredException();
        }

        if (empty($reviewId)) {
            throw ReviewTranslatorException::missingReviewId();
        }

        if (empty($languageId)) {
            throw ReviewTranslatorException::missingLanguageId();
        }

        $review = $this->getProductReview($reviewId, $languageId);

        $translatedReview = $this->getReviewTranslation($reviewId, $languageId);
        if ($translatedReview) {
            $translatedReview['language_name'] = $review['language_name'];

            return $translatedReview;
        }

        $locale = $this->getLocale($languageId);

        $response = $this->client->request(Request::METHOD_POST, self::AI_PRODUCT_REVIEW_TRANSLATION_ENDPOINT, [
            'json' => [
                'reviews' => [[
                    'id' => $review['id'],
                    'title' => $review['title'],
                    'content' => $review['content'],
                    'comment' => $review['comment'],
                ]],
                'locale' => $locale,
            ],
            'headers' => [
                'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        $content = $response->getBody()->getContents();
        if (empty($content)) {
            throw ReviewTranslatorException::emptyResponse();
        }

        /** @var array{reviews: array<int, Review>} $json */
        $json = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        $translation = $json['reviews'][0];
        $translation['language_name'] = $review['language_name'];

        $this->saveTranslation($reviewId, $languageId, $translation);

        return $translation;
    }

    /**
     * @return Review
     */
    private function getProductReview(string $reviewId, string $languageId): array
    {
        $sql = <<<'SQL'
            SELECT LOWER(HEX(product_review.id)) AS id, product_review.title, product_review.content, product_review.comment,
                   IFNULL(locale_translation.name, fallback_translation.name) AS language_name
            FROM product_review
            LEFT JOIN language ON language.id = product_review.language_id
                LEFT JOIN locale_translation ON locale_translation.locale_id = language.locale_id
                    AND locale_translation.language_id = :languageId
                LEFT JOIN locale_translation fallback_translation ON fallback_translation.locale_id = language.locale_id
                    AND fallback_translation.language_id = :fallbackLanguageId
            WHERE product_review.id = :id AND product_review.status = 1
        SQL;

        /** @var Review|false $review */
        $review = $this->connection->fetchAssociative($sql, [
            'id' => Uuid::fromHexToBytes($reviewId),
            'languageId' => Uuid::fromHexToBytes($languageId),
            'fallbackLanguageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
        ]);

        if (!\is_array($review)) {
            throw ReviewTranslatorException::reviewNotFound($reviewId);
        }

        return $review;
    }

    private function getLocale(string $languageId): string
    {
        /** @var string|null $locale */
        $locale = $this->connection->fetchOne(
            'SELECT locale.code FROM `locale` INNER JOIN `language` ON locale.id = language.locale_id  WHERE language.id = :languageId',
            ['languageId' => Uuid::fromHexToBytes($languageId)]
        );

        if ($locale === null) {
            throw ReviewTranslatorException::localeNotFound($languageId);
        }

        return $locale;
    }

    /**
     * @return array{id: string, title: string, content: string, comment: string}|null
     */
    private function getReviewTranslation(string $reviewId, string $languageId): ?array
    {
        /** @var array{id: string, title: string, content: string, comment: string}|false $result */
        $result = $this->connection->fetchAssociative(
            'SELECT LOWER(HEX(review_id)) AS id, title, content, comment FROM `product_review_translation` WHERE review_id = :reviewId AND language_id = :languageId LIMIT 1',
            ['reviewId' => Uuid::fromHexToBytes($reviewId), 'languageId' => Uuid::fromHexToBytes($languageId)]
        );

        return $result ?: null;
    }

    /**
     * @param Review $translation
     */
    private function saveTranslation(string $reviewId, string $languageId, array $translation): void
    {
        $this->connection->executeStatement(
            'REPLACE INTO `product_review_translation` (id, review_id, language_id, title, content, comment, created_at)
                    VALUES (:id, :reviewId, :languageId, :title, :content, :comment, :created_at)',
            [
                'id' => Uuid::randomBytes(),
                'reviewId' => Uuid::fromHexToBytes($reviewId),
                'languageId' => Uuid::fromHexToBytes($languageId),
                'title' => $translation['title'],
                'content' => $translation['content'],
                'comment' => $translation['comment'],
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
    }
}

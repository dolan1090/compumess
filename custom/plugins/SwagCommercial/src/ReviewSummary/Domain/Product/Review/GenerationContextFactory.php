<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Domain\Product\Review;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReviewSummary\Exception\ReviewSummaryException;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @final
 *
 * @internal
 */
#[Package('inventory')]
class GenerationContextFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $productReviewRepository
    ) {
    }

    /**
     * @param array<mixed, mixed> $generationContext
     */
    public function create(array $generationContext, Context $context): GenerationContext
    {
        $generationContext = $this->enrich($generationContext, $context);

        // Array format is checked inside `enrich` method
        return new GenerationContext(
            $generationContext['productId'],
            $generationContext['locales'],
            $generationContext['reviews'],
            $generationContext['salesChannelId'],
            $generationContext['length'] ?? 300,
            $generationContext['mood'] ?? null,
            $generationContext['languageIds'] ?? [],
            $generationContext['allowOverwrite'] ?? true,
        );
    }

    /**
     * @param array<mixed, mixed> $generationContext
     *
     * @return array{productId: string, locales: array<int|string, mixed>, reviews: array<mixed, mixed>, salesChannelId: string, length?: ?int, mood?: ?string, languageIds?: ?array<mixed, string>, allowOverwrite?: ?bool}
     */
    private function enrich(array $generationContext, Context $context): array
    {
        if (!isset($generationContext['salesChannelId']) || !\is_string($generationContext['salesChannelId'])) {
            throw ReviewSummaryException::invalidArgumentException('Missing required parameter salesChannelId.');
        }

        if (!isset($generationContext['productId']) || !\is_string($generationContext['productId'])) {
            throw ReviewSummaryException::invalidArgumentException('Missing required parameter productId.');
        }

        if (empty($generationContext['reviews'])) {
            $generationContext['reviews'] = $this->getReviews(
                $generationContext['productId'],
                $context
            );
        }

        if (empty($generationContext['reviews'])) {
            throw ReviewSummaryException::noReviewsFound($generationContext['productId']);
        }

        if (!isset($generationContext['locales']) && !isset($generationContext['languageIds'])) {
            throw ReviewSummaryException::invalidArgumentException('Missing required parameter languageIds or locales. At least one has to be provided');
        }

        if (isset($generationContext['locales']) && !\is_array($generationContext['locales'])) {
            throw ReviewSummaryException::invalidArgumentException('Parameter locales has to be an array.');
        }

        if (isset($generationContext['languageIds']) && !\is_array($generationContext['languageIds'])) {
            throw ReviewSummaryException::invalidArgumentException('Parameter languageIds has to be an array.');
        }

        if (isset($generationContext['languageIds']) && \is_array($generationContext['languageIds'])) {
            if (!isset($generationContext['locales']) || !\is_array($generationContext['locales'])) {
                $generationContext['locales'] = [];
            }

            $mappedLanguageIds = [];
            /** @var string $languageId */
            foreach ($generationContext['languageIds'] as $languageId) {
                $locale = $this->getLocale($languageId);
                if (!\in_array($locale, $generationContext['locales'], true)) {
                    $generationContext['locales'][] = $locale;
                    $mappedLanguageIds[$locale] = $languageId;
                }
            }
            $generationContext['languageIds'] = $mappedLanguageIds;
        }

        if (!\is_array($generationContext['locales']) || \count($generationContext['locales']) === 0) {
            throw ReviewSummaryException::invalidArgumentException('Could not find matching locales.');
        }

        if (isset($generationContext['length']) && !\is_int($generationContext['length'])) {
            throw ReviewSummaryException::invalidArgumentException('Parameter length has to be an integer.');
        }

        if (isset($generationContext['mood']) && !\is_string($generationContext['mood'])) {
            throw ReviewSummaryException::invalidArgumentException('Parameter mood has to be a string.');
        }

        return $generationContext; /* @phpstan-ignore-line */
    }

    private function getLocale(string $languageId): string
    {
        /** @var string|null $locale */
        $locale = $this->connection->fetchOne(
            'SELECT locale.code FROM locale INNER JOIN language ON locale.id = language.locale_id  WHERE language.id = :languageId',
            ['languageId' => Uuid::fromHexToBytes($languageId)]
        );

        if ($locale === null) {
            throw ReviewSummaryException::invalidArgumentException(sprintf('Could not find locale for languageId "%s"', $languageId));
        }

        return $locale;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getReviews(string $productId, Context $context): array
    {
        $criteria = new Criteria();
        $criteria->setTitle('product-review-summary');

        $criteria->addFilter(
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('status', true),
                new MultiFilter(MultiFilter::CONNECTION_OR, [
                    new EqualsFilter('product.id', $productId),
                    new EqualsFilter('product.parentId', $productId),
                ]),
            ])
        );

        $result = $this->productReviewRepository->search($criteria, $context);
        $reviews = [];

        /** @var ProductReviewEntity $review */
        foreach ($result->getElements() as $review) {
            $reviews[] = [
                'title' => $review->getTitle(),
                'content' => $review->getContent(),
                'points' => (string) $review->getPoints(),
            ];
        }

        return $reviews;
    }
}

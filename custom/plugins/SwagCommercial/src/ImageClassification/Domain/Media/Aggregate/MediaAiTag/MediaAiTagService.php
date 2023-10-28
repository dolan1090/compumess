<?php

declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag;

use GuzzleHttp\ClientInterface;
use Shopware\Commercial\ImageClassification\Domain\Media\Service\MediaAltTextService;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

#[Package('administration')]
class MediaAiTagService
{
    private const IMAGE_CLASSIFICATION_URL = 'https://ai-image-classification.apps.shopware.io/api/media/meta/tags';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $mediaRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $languageRepository,
        private readonly EntityRepository $mediaAiTagRepository,
        private readonly MediaAltTextService $mediaAltTextService,
        private readonly ClientInterface $client
    ) {
    }

    public function markForAnalysis(string $mediaId, Context $context, bool $analyze = true): void
    {
        $mediaAiTagId = $this->getMediaAiTagIdByMediaId($mediaId, $context);

        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($mediaId, $mediaAiTagId, $analyze): void {
            $this->mediaRepository->update([
                [
                    'id' => $mediaId,
                    'mediaAiTag' => [
                        'id' => $mediaAiTagId,
                        'needsAnalysis' => $analyze,
                    ],
                ],
            ], $systemContext);
        });
    }

    public function analyze(string $imgBytes, string $mediaId, Context $context): void
    {
        [$targetLanguage, $targetLanguageId, $defaultLanguage] = $this->getRequestLanguages($context);

        $response = $this->client->request(Request::METHOD_POST, self::IMAGE_CLASSIFICATION_URL, [
            'json' => [
                'default_language_id' => Defaults::LANGUAGE_SYSTEM,
                'default_language' => $defaultLanguage,
                'target_language_id' => $targetLanguageId,
                'target_language' => $targetLanguage,
                'items' => [
                    [
                        'id' => $mediaId,
                        'blob' => $imgBytes,
                    ],
                ],
            ],
            'headers' => [
                'Authorization' => $this->systemConfigService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        /** @var array{ results: array<int, array{ id: string, languages: array<array{ id: string, code:string, tags: string[]}>}>} $data */
        $data = \json_decode($response->getBody()->getContents(), true, \JSON_THROW_ON_ERROR);

        $result = $data['results'][0];
        if ($result['id'] !== $mediaId) {
            return;
        }

        $translations = [];
        foreach ($result['languages'] as $language) {
            $translations[] = [
                'languageId' => $language['id'],
                'tags' => $language['tags'],
            ];
        }

        $mediaAiTagId = $this->getMediaAiTagIdByMediaId($mediaId, $context);

        $this->mediaRepository->update([
            [
                'id' => $mediaId,
                'mediaAiTag' => [
                    'id' => $mediaAiTagId,
                    'translations' => $translations,
                ],
            ],
        ], $context);

        $this->mediaAltTextService->updateMediaAltText($mediaId, $targetLanguageId, $context);
    }

    /**
     * @return string[]
     */
    private function getRequestLanguages(Context $context): array
    {
        $defaultLanguage = $this->getLanguageById(Defaults::LANGUAGE_SYSTEM, $context);
        if ($defaultLanguage === null) {
            throw new \RuntimeException('Could not determine default language. Aborting.');
        }

        $defaultLocale = $defaultLanguage->getLocale();
        if ($defaultLocale === null) {
            throw new \RuntimeException('Could not determine locale for default language. Aborting.');
        }
        $defaultLocale = \substr($defaultLocale->getCode(), 0, 2);

        $targetLanguage = $this->getLanguageById($this->systemConfigService->getString('core.mediaAiTag.targetLanguageId'), $context);
        if ($targetLanguage === null) {
            return [
                $defaultLocale,
                Defaults::LANGUAGE_SYSTEM,
                $defaultLocale,
            ];
        }

        $targetLocale = $targetLanguage->getLocale();
        if ($targetLocale === null) {
            return [
                $defaultLocale,
                Defaults::LANGUAGE_SYSTEM,
                $defaultLocale,
            ];
        }

        return [
            \substr($targetLocale->getCode(), 0, 2),
            $targetLanguage->getId(),
            $defaultLocale,
        ];
    }

    private function getLanguageById(string $languageId, Context $context): ?LanguageEntity
    {
        $criteria = new Criteria([$languageId]);
        $criteria->addAssociation('locale');

        /** @var LanguageEntity|null */
        $language = $this->languageRepository->search($criteria, $context)->get($languageId);

        return $language;
    }

    private function getMediaAiTagIdByMediaId(string $mediaId, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('mediaId', $mediaId)
        );

        return $this->mediaAiTagRepository->searchIds($criteria, $context)->firstId();
    }
}

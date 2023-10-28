<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Service;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\Aggregate\MediaAiTagTranslation\MediaAiTagTranslationEntity;
use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MediaAiTagEntity;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Media\Aggregate\MediaTranslation\MediaTranslationEntity;
use Shopware\Core\Content\Media\Exception\MediaNotFoundException;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;

#[Package('administration')]
class MediaAltTextService
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $mediaRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $mediaAiTagRepository
    ) {
    }

    public function updateMediaAltText(string $mediaId, string $languageId, Context $context): void
    {
        if (!License::get('IMAGE_CLASSIFICATION-0171982')) {
            throw new LicenseExpiredException();
        }

        $shouldAddToAltText = $this->systemConfigService->getBool('core.mediaAiTag.addToAltText');
        if (!$shouldAddToAltText) {
            return;
        }

        $knownStrategies = [
            'overwrite',
            'append',
            'prepend',
            'keep',
        ];
        $strategy = $this->systemConfigService->getString('core.mediaAiTag.altTextStrategy');

        if (!\in_array($strategy, $knownStrategies, true) || $strategy === 'keep') {
            return;
        }

        $defaultLanguageAiText = $this->builtAiAltText($mediaId, Defaults::LANGUAGE_SYSTEM, $context);
        $targetLanguageAiText = $this->builtAiAltText($mediaId, $languageId, $context);
        if ($targetLanguageAiText === null || $defaultLanguageAiText === null) {
            return;
        }

        switch ($strategy) {
            case 'overwrite':
                $this->overwriteAltText($mediaId, $languageId, $targetLanguageAiText, $defaultLanguageAiText, $context);

                break;
            case 'append':
                $this->appendAltText($mediaId, $languageId, $targetLanguageAiText, $defaultLanguageAiText, $context);

                break;
            case 'prepend':
                $this->prependAltText($mediaId, $languageId, $targetLanguageAiText, $defaultLanguageAiText, $context);

                break;
        }
    }

    private function overwriteAltText(string $mediaId, string $languageId, string $targetLanguageAiText, string $defaultLanguageAiText, Context $context): void
    {
        $this->mediaRepository->update([
            [
                'id' => $mediaId,
                'translations' => [
                    [
                        'mediaId' => $mediaId,
                        'languageId' => $languageId,
                        'alt' => $targetLanguageAiText,
                    ],
                    [
                        'mediaId' => $mediaId,
                        'languageId' => Defaults::LANGUAGE_SYSTEM,
                        'alt' => $defaultLanguageAiText,
                    ],
                ],
            ],
        ], $context);
    }

    private function appendAltText(string $mediaId, string $languageId, string $targetLanguageAiText, string $defaultLanguageAiText, Context $context): void
    {
        $targetLanguageAltText = $this->getCurrentAltText($mediaId, $languageId, $context);
        $defaultLanguageAltText = $this->getCurrentAltText($mediaId, Defaults::LANGUAGE_SYSTEM, $context);

        $this->mediaRepository->update([
            [
                'id' => $mediaId,
                'translations' => [
                    [
                        'mediaId' => $mediaId,
                        'languageId' => $languageId,
                        'alt' => $targetLanguageAltText ? \sprintf('%s, %s', $targetLanguageAltText, $targetLanguageAiText) : $targetLanguageAiText,
                    ],
                    [
                        'mediaId' => $mediaId,
                        'languageId' => Defaults::LANGUAGE_SYSTEM,
                        'alt' => $defaultLanguageAltText ? \sprintf('%s, %s', $defaultLanguageAltText, $defaultLanguageAiText) : $defaultLanguageAiText,
                    ],
                ],
            ],
        ], $context);
    }

    private function prependAltText(string $mediaId, string $languageId, string $targetLanguageAiText, string $defaultLanguageAiText, Context $context): void
    {
        $targetLanguageAltText = $this->getCurrentAltText($mediaId, $languageId, $context);
        $defaultLanguageAltText = $this->getCurrentAltText($mediaId, Defaults::LANGUAGE_SYSTEM, $context);

        $this->mediaRepository->update([
            [
                'id' => $mediaId,
                'translations' => [
                    [
                        'mediaId' => $mediaId,
                        'languageId' => $languageId,
                        'alt' => \sprintf('%s, %s', $targetLanguageAiText, $targetLanguageAltText),
                    ],
                    [
                        'mediaId' => $mediaId,
                        'languageId' => Defaults::LANGUAGE_SYSTEM,
                        'alt' => \sprintf('%s, %s', $defaultLanguageAiText, $defaultLanguageAltText),
                    ],
                ],
            ],
        ], $context);
    }

    private function getCurrentAltText(string $mediaId, string $languageId, Context $context): string
    {
        $criteria = new Criteria([$mediaId]);
        $criteria->addAssociation('translations');
        $criteria->setLimit(1);

        /** @var MediaEntity|null $media */
        $media = $this->mediaRepository->search($criteria, $context)->first();
        if ($media === null) {
            throw new MediaNotFoundException($mediaId);
        }

        /** @var MediaTranslationEntity|null $translation */
        $translation = $media->getTranslations()?->filterByProperty('languageId', $languageId)->first();
        if ($translation === null) {
            return '';
        }

        return $translation->getAlt() ?: '';
    }

    private function builtAiAltText(string $mediaId, string $languageId, Context $context): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('mediaId', $mediaId)
        );
        $criteria->addAssociation('translations');
        $criteria->setLimit(1);

        /** @var MediaAiTagEntity|null $mediaAiTag */
        $mediaAiTag = $this->mediaAiTagRepository->search($criteria, $context)->first();
        if ($mediaAiTag === null) {
            return null;
        }

        /** @var MediaAiTagTranslationEntity|null $translation */
        $translation = $mediaAiTag->getTranslations()?->filterByProperty('languageId', $languageId)->first();
        if ($translation === null) {
            return null;
        }

        $tags = $translation->getTags();
        if (empty($tags)) {
            return null;
        }

        return implode(', ', $tags);
    }
}

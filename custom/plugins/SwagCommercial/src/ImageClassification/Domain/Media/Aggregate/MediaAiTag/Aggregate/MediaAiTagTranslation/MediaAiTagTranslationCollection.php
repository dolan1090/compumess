<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\Aggregate\MediaAiTagTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MediaAiTagTranslationEntity>
 */
#[Package('administration')]
class MediaAiTagTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'media_ai_tag_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaAiTagTranslationEntity::class;
    }
}

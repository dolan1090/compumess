<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MediaAiTagEntity>
 */
#[Package('administration')]
class MediaAiTagCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'media_ai_tag_collection';
    }

    protected function getExpectedClass(): string
    {
        return MediaAiTagEntity::class;
    }
}

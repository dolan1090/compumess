<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\Aggregate\MediaAiTagTranslation;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MediaAiTagEntity;
use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;
use Shopware\Core\Framework\Log\Package;

#[Package('administration')]
class MediaAiTagTranslationEntity extends TranslationEntity
{
    protected string $mediaAiTagId;

    /**
     * @var string[]|null
     */
    protected array|null $tags = null;

    protected MediaAiTagEntity|null $mediaAiTag = null;

    public function getMediaAiTagId(): string
    {
        return $this->mediaAiTagId;
    }

    public function setMediaAiTagId(string $mediaAiTagId): void
    {
        $this->mediaAiTagId = $mediaAiTagId;
    }

    /**
     * @return string[]
     */
    public function getTags(): ?array
    {
        return $this->tags;
    }

    /**
     * @param string[] $tags
     */
    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getMediaAiTag(): ?MediaAiTagEntity
    {
        return $this->mediaAiTag;
    }

    public function setMediaAiTag(?MediaAiTagEntity $mediaAiTag): void
    {
        $this->mediaAiTag = $mediaAiTag;
    }
}

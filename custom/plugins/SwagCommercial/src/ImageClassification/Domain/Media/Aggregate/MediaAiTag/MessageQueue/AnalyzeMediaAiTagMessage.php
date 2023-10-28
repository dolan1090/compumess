<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MessageQueue;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('administration')]
class AnalyzeMediaAiTagMessage implements AsyncMessageInterface
{
    public function __construct(private readonly string $mediaId)
    {
    }

    public function getMediaId(): string
    {
        return $this->mediaId;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MessageQueue;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MediaAiTagService;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler]
#[Package('administration')]
final class AnalyzeMediaAiTagHandler
{
    /**
     * @internal
     */
    public function __construct(
        private readonly MediaService $mediaService,
        private readonly MediaAiTagService $mediaAiTagService
    ) {
    }

    public function __invoke(AnalyzeMediaAiTagMessage $message): void
    {
        $context = Context::createDefaultContext();
        $file = \base64_encode($this->mediaService->loadFile($message->getMediaId(), $context));

        try {
            $this->mediaAiTagService->analyze($file, $message->getMediaId(), $context);
        } catch (\Exception $e) {
            // Silent catch to prevent retries
        }

        $this->mediaAiTagService->markForAnalysis($message->getMediaId(), $context, false);
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Subscriber;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MediaAiTagService;
use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MessageQueue\AnalyzeMediaAiTagMessage;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Media\MediaEvents;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[Package('administration')]
class MediaWrittenSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly MessageBusInterface $messageBus,
        private readonly MediaAiTagService $mediaAiTagService
    ) {
    }

    /**
     * @return array|array[]|\array[][]|mixed[]|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MediaEvents::MEDIA_WRITTEN_EVENT => 'onMediaWrittenEvent',
        ];
    }

    public function onMediaWrittenEvent(EntityWrittenEvent $event): void
    {
        if (!License::get('IMAGE_CLASSIFICATION-0171982')) {
            return;
        }

        if (!$this->systemConfigService->getBool('core.mediaAiTag.enabled')) {
            return;
        }

        /** @var EntityWriteResult $writeResult */
        foreach ($event->getWriteResults() as $writeResult) {
            $entityExistence = $writeResult->getExistence();
            if (!$entityExistence || !$entityExistence->exists()) {
                continue;
            }

            $payload = $writeResult->getPayload();

            // Check if media is jpg or png
            $validMimeTypes = [
                'image/jpeg',
                'image/png',
            ];
            if (!\array_key_exists('mimeType', $payload) || !\in_array($payload['mimeType'], $validMimeTypes, true)) {
                continue;
            }

            // Check if media is larger than 5mb
            if (!\array_key_exists('fileSize', $payload) || $payload['fileSize'] > 5000000) {
                continue;
            }

            $primaryKey = $writeResult->getPrimaryKey();
            if (\is_array($primaryKey)) {
                continue;
            }

            $this->mediaAiTagService->markForAnalysis($primaryKey, $event->getContext());

            $this->messageBus->dispatch(new AnalyzeMediaAiTagMessage($primaryKey));
        }
    }
}

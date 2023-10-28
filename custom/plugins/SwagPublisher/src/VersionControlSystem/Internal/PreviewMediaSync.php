<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PreviewMediaSync implements EventSubscriberInterface
{
    private VersionControlCmsGateway $cmsGateway;

    public function __construct(
        VersionControlCmsGateway $cmsGateway
    ) {
        $this->cmsGateway = $cmsGateway;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CmsPageEvents::PAGE_WRITTEN_EVENT => 'updatePreviewMediaId',
        ];
    }

    public function updatePreviewMediaId(EntityWrittenEvent $event): void
    {
        $payloads = [];
        $writeResults = $event->getWriteResults();

        foreach ($writeResults as $writeResult) {
            if ($writeResult->hasPayload('previewMediaId')) {
                $key = $writeResult->getPrimaryKey();

                if (\is_array($key)) {
                    continue;
                }

                $payloads[$key] = $writeResult->getPayload();
            }
        }

        if (!$payloads) {
            return;
        }

        $context = $event->getContext();

        $drafts = $this->cmsGateway
            ->searchDrafts(self::createCriteriaFromPayloads($payloads), $context);

        if (!$drafts->count()) {
            return;
        }

        $draftData = [];
        foreach ($drafts as $draft) {
            $draftData[] = [
                'id' => $draft->getId(),
                'previewMediaId' => $payloads[$draft->get('pageId')]['previewMediaId'],
            ];
        }

        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($draftData): void {
            $this->cmsGateway->updateDrafts($draftData, $systemContext);
        });
    }

    private static function createCriteriaFromPayloads(array $payloads): Criteria
    {
        $filters = [];
        foreach ($payloads as $payload) {
            $filters[] = new AndFilter([
                new EqualsFilter('pageId', $payload['id']),
                new EqualsFilter('draftVersion', $payload['versionId']),
            ]);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, $filters));

        return $criteria;
    }
}

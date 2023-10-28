<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use SwagPublisher\VersionControlSystem\Data\ActivityCollection;
use SwagPublisher\VersionControlSystem\Data\DraftCollection;

class VersionControlCmsGateway
{
    private const FALLBACK_DRAFT_NAME = '-';

    public function __construct(
        private readonly EntityRepository $draftRepository,
        private readonly EntityRepository $activityRepository,
        private readonly EntityRepository $pageRepository
    ) {
    }

    public function searchActivities(Criteria $criteria, Context $context): ActivityCollection
    {
        /** @var ActivityCollection $activityCollection */
        $activityCollection = $this->activityRepository->search($criteria, $context)->getEntities();

        return $activityCollection;
    }

    public function updateActivities(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->activityRepository
            ->update($data, $context);
    }

    public function createActivities(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->activityRepository
            ->create($data, $context);
    }

    public function searchDrafts(Criteria $criteria, Context $context): DraftCollection
    {
        /** @var DraftCollection $draftCollection */
        $draftCollection = $this->draftRepository->search($criteria, $context)->getEntities();

        return $draftCollection;
    }

    public function updateDrafts(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->draftRepository
            ->update($data, $context);
    }

    public function createDrafts(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->draftRepository
            ->create($data, $context);
    }

    public function deleteDrafts(array $data, Context $context): EntityWrittenContainerEvent
    {
        return $this->draftRepository
            ->delete($data, $context);
    }

    public function fetchInheritedDraftData(string $id, Context $context): array
    {
        /** @var CmsPageEntity $page */
        $page = $this->pageRepository
            ->search(CriteriaFactory::withIds($id), $context)
            ->first();

        return [
            'name' => $this->getCmsPageName($page),
            'pageId' => $page->getId(),
            'previewMediaId' => $page->getPreviewMediaId(),
        ];
    }

    private function getCmsPageName(CmsPageEntity $page): string
    {
        $name = $page->getName();

        return empty($name) ? self::FALLBACK_DRAFT_NAME : $name;
    }
}

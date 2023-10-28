<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsPageTranslation\CmsPageTranslationDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationDefinition;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use SwagPublisher\Common\UpdateChangeContextExtension;
use SwagPublisher\VersionControlSystem\Exception\NoDraftFound;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivityLogSubscriber implements EventSubscriberInterface
{
    /**
     * @var string[]
     */
    private array $pageIdMap = [];

    public function __construct(
        private readonly VersionControlCmsGateway $versionControlCmsGateway,
        private readonly EntityRepository $pageRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // update & insert
            CmsPageEvents::PAGE_WRITTEN_EVENT => 'logActivityOnCmsWriteEvent',
            CmsSectionDefinition::ENTITY_NAME . '.written' => 'logActivityOnCmsWriteEvent',
            CmsPageEvents::SLOT_WRITTEN_EVENT => 'logActivityOnCmsWriteEvent',
            CmsPageEvents::BLOCK_WRITTEN_EVENT => 'logActivityOnCmsWriteEvent',
            CmsSlotTranslationDefinition::ENTITY_NAME . '.written' => 'logActivityOnCmsWriteEvent',
            CmsPageTranslationDefinition::ENTITY_NAME . '.written' => 'logActivityOnCmsWriteEvent',

            // delete
            EntityDeleteEvent::class => 'logActivityOnCmsDeleteEvent',
        ];
    }

    public function logActivityOnCmsDeleteEvent(EntityDeleteEvent $event): void
    {
        $writeResults = \array_merge(
            $this->getDeletedIds($event, CmsBlockDefinition::ENTITY_NAME),
            $this->getDeletedIds($event, CmsSectionDefinition::ENTITY_NAME),
            $this->getDeletedIds($event, CmsSlotDefinition::ENTITY_NAME)
        );

        if (empty($writeResults)) {
            return;
        }

        $context = $event->getContext();

        // store ids on order to be able to log them in the next step
        $this->storePageIds($writeResults, $context);

        $filteredWriteResults = $this->filterOnlyRememberedWriteResults($writeResults);
        $this->writeLogActivity($filteredWriteResults, $context);
    }

    public function logActivityOnCmsWriteEvent(EntityWrittenEvent $event): void
    {
        $context = $event->getContext();
        $writeResults = $event->getWriteResults();

        $this->storePageIds($writeResults, $context);
        $this->writeLogActivity($writeResults, $context);
    }

    public function containsChangedData(EntityWriteResult $writeResult, Context $context): bool
    {
        if ($writeResult->getOperation() !== EntityWriteResult::OPERATION_UPDATE) {
            return true;
        }

        return UpdateChangeContextExtension::extract($context)->hasChanges($writeResult);
    }

    /**
     * @param EntityWriteResult[] $writeResults
     */
    private function storePageIds(array $writeResults, Context $context): void
    {
        foreach ($writeResults as $writeResult) {
            $affectedEntity = WriteResultExtractor::extractAffectedEntity($writeResult);
            $key = $this->getIdMapKey($affectedEntity);

            if (!($cmsPageId = $this->fetchCmsPageIdByAffectedEntity($affectedEntity, $context))) {
                continue;
            }

            $this->pageIdMap[$key] = $cmsPageId;
        }
    }

    /**
     * @param EntityWriteResult[] $writeResults
     */
    private function writeLogActivity(array $writeResults, Context $context): void
    {
        $source = $context->getSource();

        if (!$source instanceof AdminApiSource) {
            return;
        }

        try {
            $draftVersion = $this->determineDraftVersion($context);
        } catch (NoDraftFound $exception) {
            return;
        }

        $pageIdToDetailMap = $this->extractDetails($writeResults, $context);
        $pageIdToActivityMap = $this->loadActivities($pageIdToDetailMap, $draftVersion, $context);

        $this->writeActivityDetails($pageIdToDetailMap, $pageIdToActivityMap, $source, $draftVersion, $context);

        $this->pageIdMap = [];
    }

    private function createNewActivity(string $pageId, ?string $draftVersion, array $details, Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($details, $pageId, $draftVersion): void {
            $source = $systemContext->getSource();

            if (!$source instanceof AdminApiSource) {
                return;
            }

            $userId = $source->getUserId();

            $this->versionControlCmsGateway->createActivities([[
                'draftVersion' => $draftVersion,
                'details' => $details,
                'pageId' => $pageId,
                'userId' => $userId,
                'name' => $this->fetchDraftName($pageId, $draftVersion, $systemContext),
            ]], $systemContext->createWithVersionId(Defaults::LIVE_VERSION));
        });
    }

    private function updateExistingActivity(ArrayEntity $activity, array $details, Context $context): void
    {
        $context->scope(Context::SYSTEM_SCOPE, function (Context $systemContext) use ($activity, $details): void {
            $this->versionControlCmsGateway->updateActivities([[
                'id' => $activity->getId(),
                'details' => $this->mergeActivityDetails($details, $activity['details']),
            ]], $systemContext->createWithVersionId(Defaults::LIVE_VERSION));
        });
    }

    private function extractDetailsFromWriteResults(EntityWriteResult $writeResult, Context $context): ?array
    {
        $payload = $writeResult->getPayload();

        if ($this->isAllowedToSkip($writeResult, $context)) {
            return null;
        }

        return [
            'id' => $writeResult->getPrimaryKey(),
            'name' => $payload['name'] ?? null,
            'operation' => $writeResult->getOperation(),
            'entityName' => $writeResult->getEntityName(),
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
        ];
    }

    private function isAllowedToSkip(EntityWriteResult $writeResult, Context $context): bool
    {
        return $this->containsTranslationInsertion($writeResult) || !$this->containsChangedData($writeResult, $context);
    }

    private function fetchCmsPageIdByAffectedEntity(AffectedEntity $affectedEntity, Context $context): ?string
    {
        $entityName = $affectedEntity->getName();
        $id = $affectedEntity->getId();

        switch ($entityName) {
            case CmsPageDefinition::ENTITY_NAME:
                $criteria = CriteriaFactory::withIds($id);

                break;
            case CmsBlockDefinition::ENTITY_NAME:
                $criteria = CriteriaFactory::forPageByBlockId($id);

                break;
            case CmsSlotDefinition::ENTITY_NAME:
                $criteria = CriteriaFactory::forPageBySlotId($id);

                break;
            case CmsSectionDefinition::ENTITY_NAME:
                $criteria = CriteriaFactory::forPageBySectionId($id);

                break;
            default:
                return null;
        }

        /** @var CmsPageEntity $cmsPage */
        $cmsPage = $this->pageRepository->search($criteria, $context)->first();

        return $cmsPage->getId();
    }

    private function mergeActivityDetails(array $newDetails, ?array $activityDetails): array
    {
        if (!$activityDetails) {
            return $newDetails;
        }

        return \array_merge(\array_reverse($newDetails), $activityDetails);
    }

    private function fetchDraftActivity(string $pageId, ?string $draftVersion, Context $context): ?ArrayEntity
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('pageId', $pageId),
                new EqualsFilter('draftVersion', $draftVersion),
            ])
        );

        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        return $this->versionControlCmsGateway->searchActivities($criteria, $context->createWithVersionId(Defaults::LIVE_VERSION))
            ->first();
    }

    private function containsTranslationInsertion(EntityWriteResult $writeResult): bool
    {
        return $writeResult->getOperation() === EntityWriteResult::OPERATION_INSERT
            && WriteResultExtractor::isTranslation($writeResult);
    }

    private function determineDraftVersion(Context $context): ?string
    {
        if ($context->getVersionId() === Defaults::LIVE_VERSION) {
            return null;
        }

        $draftVersion = $context->getVersionId();

        $drafts = $this->versionControlCmsGateway
            ->searchDrafts(
                CriteriaFactory::forDraftWithVersion($draftVersion),
                $context->createWithVersionId(Defaults::LIVE_VERSION)
            );

        if (!$drafts->count()) {
            throw new NoDraftFound();
        }

        return $draftVersion;
    }

    /**
     * @param EntityWriteResult[] $writeResults
     */
    private function extractDetails(array $writeResults, Context $context): array
    {
        $pageIdToDetailMap = [];
        foreach ($writeResults as $writeResult) {
            $affectedEntity = WriteResultExtractor::extractAffectedEntity($writeResult);
            $cmsPageId = $this->pageIdMap[$this->getIdMapKey($affectedEntity)];

            $details = $this->extractDetailsFromWriteResults($writeResult, $context);

            if (!$details) {
                continue;
            }

            $this->updateDetailsByAffectedEntity($details, $affectedEntity);

            if (!isset($pageIdToDetailMap[$cmsPageId])) {
                $pageIdToDetailMap[$cmsPageId] = [];
            }

            $pageIdToDetailMap[$cmsPageId][] = $details;
        }

        return $pageIdToDetailMap;
    }

    private function updateDetailsByAffectedEntity(array &$details, AffectedEntity $affectedEntity): void
    {
        if ($details['id'] === $affectedEntity->getId()) {
            return;
        }

        $details['id'] = $affectedEntity->getId();
        $details['entityName'] = $affectedEntity->getName();
    }

    private function loadActivities(array $pageIdToDetailMap, ?string $draftVersion, Context $context): array
    {
        $pageIdToActivityMap = [];
        foreach ($pageIdToDetailMap as $cmsPageId => $null) {
            $pageIdToActivityMap[$cmsPageId] = $this
                ->fetchDraftActivity($cmsPageId, $draftVersion, $context);
        }

        return $pageIdToActivityMap;
    }

    private function writeActivityDetails(
        array $pageIdToDetailMap,
        array $pageIdToActivityMap,
        AdminApiSource $source,
        ?string $draftVersion,
        Context $context
    ): void {
        foreach ($pageIdToDetailMap as $cmsPageId => $details) {
            $activity = $pageIdToActivityMap[$cmsPageId];

            if (!$activity || $activity['userId'] !== $source->getUserId()) {
                $this->createNewActivity($cmsPageId, $draftVersion, $details, $context);

                continue;
            }

            $this->updateExistingActivity($activity, $details, $context);
        }
    }

    private function getIdMapKey(AffectedEntity $affectedEntity): string
    {
        return $affectedEntity->getName() . $affectedEntity->getId();
    }

    /**
     * @param EntityWriteResult[] $writeResults
     */
    private function filterOnlyRememberedWriteResults(array $writeResults): array
    {
        $filteredWriteResults = [];

        foreach ($writeResults as $writeResult) {
            $affectedEntity = WriteResultExtractor::extractAffectedEntity($writeResult);
            $key = $this->getIdMapKey($affectedEntity);

            if (!isset($this->pageIdMap[$key])) {
                continue;
            }

            $filteredWriteResults[] = $writeResult;
        }

        return $filteredWriteResults;
    }

    private function fetchDraftName(string $pageId, ?string $draftVersion, Context $context): string
    {
        if (!$draftVersion) {
            return $this->fetchOriginalPageName($pageId, $context);
        }

        $criteria = CriteriaFactory::forActivityWithPageAndVersion($pageId, $draftVersion);

        $activity = $this->versionControlCmsGateway
            ->searchActivities($criteria, $context)
            ->first();

        if (!$activity) {
            return $this->fetchOriginalPageName($pageId, $context);
        }

        return $activity->get('name');
    }

    private function fetchOriginalPageName(string $pageId, Context $context): string
    {
        return $this->versionControlCmsGateway
            ->fetchInheritedDraftData($pageId, $context)['name'];
    }

    /**
     * @return list<EntityWriteResult>
     */
    private function getDeletedIds(EntityDeleteEvent $event, string $entityName): array
    {
        $writeResults = [];

        foreach ($event->getIds($entityName) as $id) {
            $writeResults[] = new EntityWriteResult(
                $id,
                [],
                $entityName,
                EntityWriteResult::OPERATION_DELETE
            );
        }

        return $writeResults;
    }
}

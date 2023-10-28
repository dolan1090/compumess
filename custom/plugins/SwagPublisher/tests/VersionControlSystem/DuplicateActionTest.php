<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\DuplicateAction;
use SwagPublisher\VersionControlSystem\Exception\NoDraftFound;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisherTest\DraftIntegrationTrait;
use SwagPublisherTest\PublisherCmsFixtures;

class DuplicateActionTest extends TestCase
{
    use DraftIntegrationTrait;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testDuplicateCreatesDraftAndActivityLogEntry(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $cmsGateway = $this->getCmsGateway();
        $draftName = 'duplicate me';

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, $draftName, $context);

        $activities = $cmsGateway->searchActivities(self::getActivityCriteria(), $context);
        static::assertSame(1, $activities->count());

        $newVersionId = $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);
        static::assertNotSame($newVersionId, $versionId);

        $draftCollection = $cmsGateway
            ->searchDrafts(CriteriaFactory::forDraftWithVersion($newVersionId), $context);
        static::assertSame(1, $draftCollection->count());

        $draft = $draftCollection->first();
        static::assertNotSame($versionId, $newVersionId);
        static::assertSame($pageId, $draft->get('pageId'));
        static::assertSame($draftName, $draft->get('name'));

        $activities = $cmsGateway->searchActivities(self::getActivityCriteria(), $context);
        static::assertSame(2, $activities->count());

        $activity = $activities->first();
        static::assertSame($newVersionId, $activity->get('draftVersion'));
    }

    public function testDuplicateCreatesNewLivePageAndVersion(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, null, $context);

        $newVersionId = $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);

        $newVersionPage = $this->getContainer()
            ->get('cms_page.repository')
            ->search(CriteriaFactory::forPageWithVersion($newVersionId), $context->createWithVersionId($newVersionId))
            ->first();

        static::assertSame($newVersionId, $newVersionPage->getVersionId());
        static::assertSame('enterprise', $newVersionPage->getName());
        static::assertSame($pageId, $newVersionPage->getId());

        $drafts = $this->getContainer()
            ->get('cms_page_draft.repository')
            ->search((new Criteria())->addFilter(new EqualsFilter('pageId', $pageId)), $context->createWithVersionId($newVersionId));

        static::assertCount(2, $drafts);

        $draftVersions = $drafts->map(static function (ArrayEntity $entity): string {
            return $entity['draftVersion'];
        });

        static::assertContains($newVersionId, $draftVersions);
        static::assertContains($versionId, $draftVersions);
    }

    public function testDuplicateDoesNotRemoveOriginalPageAndVersion(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, null, $context);

        $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $versionPageCollection = $cmsPageRepository
            ->search(CriteriaFactory::forPageWithVersion($versionId), $context->createWithVersionId($versionId));

        static::assertSame(1, $versionPageCollection->count());

        $livePageCollection = $cmsPageRepository
            ->search(new Criteria([$pageId]), $context);

        static::assertSame(1, $livePageCollection->count());
    }

    public function testDuplicateThrowsNotFoundException(): void
    {
        $this->expectException(NoDraftFound::class);
        $this->getContainer()->get(DuplicateAction::class)
            ->duplicate(Uuid::randomHex(), Uuid::randomHex(), Context::createDefaultContext());
    }

    public function testDuplicateWorksWithAdminApiSource(): void
    {
        $pageId = $this->importPage();
        $context = self::createAdminApiContext();
        $draftName = 'Duplicated with admin api';

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, $draftName, $context);

        $newVersionId = $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);

        $draftCollection = $this->getCmsGateway()
            ->searchDrafts(CriteriaFactory::forDraftWithVersion($newVersionId), $context);

        static::assertSame(1, $draftCollection->count());

        $draft = $draftCollection->first();
        static::assertSame($newVersionId, $draft->get('draftVersion'));
        static::assertSame($draftName, $draft->get('name'));
    }

    public function testDuplicateCheckWholeWrittenData(): void
    {
        $pageId = $this->importPage();
        $userId = $this->fetchFirstUserId();
        $context = self::createAdminApiContext($userId);
        $draftName = 'Draft 123';

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, $draftName, $context);

        $draft = $this->getCmsGateway()
            ->searchDrafts(CriteriaFactory::forDraftWithVersion($versionId), $context)
            ->first();

        $mediaId = $this->fetchFirstMediaId();
        $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($draft, $userId, $mediaId): void {
            $this->getCmsGateway()->updateDrafts([[
                'id' => $draft->getId(),
                'previewMediaId' => $mediaId,
                'ownerId' => $userId,
            ]], $context);
        });

        $newVersionId = $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);

        $draft = $this->getCmsGateway()
            ->searchDrafts(CriteriaFactory::forDraftWithVersion($newVersionId), $context)
            ->first();

        static::assertSame($newVersionId, $draft->get('draftVersion'));
        static::assertSame($draftName, $draft->get('name'));
        static::assertSame($context->getVersionId(), $draft->get('cmsPageVersionId'));
        static::assertSame($userId, $draft->get('ownerId'));
        static::assertSame($mediaId, $draft->get('previewMediaId'));
        static::assertNull($draft->get('updatedAt'));
    }

    public function testDuplicateWritesNameIntoNewActivityLog(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $cmsGateway = $this->getCmsGateway();
        $draftName = 'duplicate me';

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, $draftName, $context);

        $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);

        $activity = $cmsGateway->searchActivities(self::getActivityCriteria(), $context)->first();
        static::assertSame($draftName, $activity->get('name'));
    }

    public function testDuplicateWritesFallbackNameIntoNewActivityLog(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $cmsGateway = $this->getCmsGateway();

        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, null, $context);

        $this->getContainer()->get(DuplicateAction::class)
            ->duplicate($pageId, $versionId, $context);

        $activity = $cmsGateway->searchActivities(self::getActivityCriteria(), $context)->first();
        static::assertSame('enterprise', $activity->get('name'));
    }

    private static function createAdminApiContext(?string $userId = null): Context
    {
        $source = new AdminApiSource($userId);
        $source->setIsAdmin(true);

        return Context::createDefaultContext($source);
    }

    private static function getActivityCriteria(): Criteria
    {
        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));

        return $criteria;
    }

    private function getCmsGateway(): VersionControlCmsGateway
    {
        return $this->getContainer()
            ->get(VersionControlCmsGateway::class);
    }

    private function fetchFirstUserId(): string
    {
        $criteria = new Criteria();
        $criteria->setLimit(1);

        return $this->getContainer()->get('user.repository')
            ->searchIds(new Criteria(), Context::createDefaultContext())
            ->firstId();
    }
}

<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsPageTranslation\CmsPageTranslationDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationDefinition;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Internal\ActivityLogSubscriber;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use SwagPublisherTest\ContextFactory;
use SwagPublisherTest\PublisherCmsFixtures;

class ActivityLogSubscriberTest extends TestCase
{
    use ContextFactory;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testItIgnoresNonAdminApiChanges(): void
    {
        $context = Context::createDefaultContext();
        $pageId = $this->createCmsPageFixture($context);
        $activity = $this->fetchLatestActivity($pageId, $context);

        static::assertNull($activity);
    }

    public function testItAlwaysWritesALog(): void
    {
        $context = $this->createAdminApiSourceContext();
        $pageId = $this->createCmsPageFixture($context);
        $this->assertLatestActivityDetailCount($pageId, $context, 1);

        $sectionId = $this->createSectionFixture('section', $pageId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 2);
        $blockId = $this->createBlockFixture($pageId, $sectionId, $context, 'custom name 123');
        $this->assertLatestActivityDetailCount($pageId, $context, 3);
        $slotId = $this->createSlotFixture($blockId, $sectionId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 4);

        $this->deleteSlot($slotId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 5);
        $this->deleteBlock($blockId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 6);

        $this->assertTotalActivitiesForPage($pageId, 1);

        $this->deleteSection($sectionId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 7);
    }

    public function testDeletingWithCascade(): void
    {
        $context = $this->createAdminApiSourceContext();
        $pageId = $this->createCmsPageFixture($context);
        $this->assertLatestActivityDetailCount($pageId, $context, 1);

        $sectionId = $this->createSectionFixture('section', $pageId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 2);
        $blockId = $this->createBlockFixture($pageId, $sectionId, $context, 'custom name 123');
        $this->assertLatestActivityDetailCount($pageId, $context, 3);

        $this->createSlotFixture($blockId, $sectionId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 4);

        // expect to have 2 new activities (block deletion and slot deletion which was in the block)
        $this->deleteBlock($blockId, $context);
        $this->assertLatestActivityDetailCount($pageId, $context, 6);

        $this->assertTotalActivitiesForPage($pageId, 1);
    }

    public function testItIgnoresForeignKeyOnlyUpdates(): void
    {
        $context = $this->createAdminApiSourceContext();
        $pageId = $this->createCmsPageFixture();

        static::assertNull($this->fetchLatestActivity($pageId, $context));

        $sectionId = $this->createSectionFixture('section', $pageId, $context);
        $blockId = $this->createBlockFixture($pageId, $sectionId, $context, 'custom name 123');
        $slotId = $this->createSlotFixture($blockId, $sectionId, $context);

        $this->assertLatestActivityDetailCount($pageId, $context, 3);

        $this->getCmsSectionRepository()->update([[
            'id' => $sectionId,
            'blocks' => [[
                'id' => $blockId,
                'slots' => [[
                    'id' => $slotId,
                    'slot' => 'content',
                ]],
            ]], ]], $context);

        $this->assertLatestActivityDetailCount($pageId, $context, 4);
    }

    public function testItWritesMultipleLogsForMultiplePagesInOneWrite(): void
    {
        $context = $this->createAdminApiSourceContext();

        $pages = [
            $this->getCmsPageFixture()[0],
            $this->getCmsPageFixture()[0],
            $this->getCmsPageFixture()[0],
        ];

        $this->getContainer()->get('cms_page.repository')
            ->create($pages, $context);

        $this->assertTotalActivitiesForPage($pages[0]['id'], 1);
        $this->assertTotalActivitiesForPage($pages[1]['id'], 1);
        $this->assertTotalActivitiesForPage($pages[2]['id'], 1);
    }

    public function testWritingTheSameFixtureTwiceDoesNotLogActivityDetails(): void
    {
        $context = $this->createAdminApiSourceContext();

        $pages = $this->getCmsPageFixture();

        $this->getContainer()->get('cms_page.repository')
            ->create($pages, $context);
        $this->getContainer()->get('cms_page.repository')
            ->update($pages, $context);

        $this->assertLatestActivityDetailCount($pages[0]['id'], $context, 10);
    }

    public function testWritingTheSameFixtureTwiceInOneWriteDoesNotLogActivityDetails(): void
    {
        $context = $this->createAdminApiSourceContext();

        $pages = $this->getCmsPageFixture();

        $this->getContainer()->get('cms_page.repository')
            ->create($pages, $context);

        $pages[0]['name'] = __METHOD__;

        $this->getContainer()->get('cms_page.repository')
            ->update([$pages[0], $pages[0]], $context);

        $this->assertLatestActivityDetailCount($pages[0]['id'], $context, 11);
    }

    public function testNonMappedEntitiesAreNotSuppliedThroughTheEventChain(): void
    {
        $context = $this->createAdminApiSourceContext();
        $page = $this->fetchCmsPage($context);
        $category = $this->fetchCategory($context);

        $this->getCmsPageRepository()->update([
            [
                'id' => $page->getId(),
                'type' => 'foo',
                'categories' => [[
                    'id' => $category->getId(),
                    'name' => 'bar',
                ]],
            ],
        ], $context);

        $this->assertTotalActivitiesForPage($page->getId(), 1);
        $activity = $this->assertLatestActivityDetailCount($page->getId(), $context, 1);
        $this->assertDetail('cms_page', 'update', null, $activity['details'][0]);
    }

    public function testThatADraftMustExistInOrderToWriteInADraftVersion(): void
    {
        $context = $this->createAdminApiSourceContext();
        $page = $this->fetchCmsPage($context);
        $versionContext = $this->branchCmsPage($page->getId(), $context, false);

        $this->createSectionFixture('test', $page->getId(), $versionContext);

        $this->assertTotalActivitiesForPage($page->getId(), 0);
    }

    public function testWriteActivitiesForMultipleBlockOperations(): void
    {
        $context = $this->createAdminApiSourceContext();
        $page = $this->fetchCmsPage($context);
        $versionContext = $this->branchCmsPage($page->getId(), $context);

        $sectionId = $this->createSectionFixture('section', $page->getId(), $versionContext);
        $blockIdToUpdate = $this->createBlockFixture($page->getId(), $sectionId, $versionContext, 'custom name 123');
        $blockIdToDelete = $this->createBlockFixture($page->getId(), $sectionId, $versionContext);

        $cmsBlockRepository = $this->getCmsBlockRepository();

        $cmsBlockRepository->update([[
            'id' => $blockIdToUpdate,
            'type' => 'test',
            'name' => 'updated name',
        ]], $versionContext);

        $cmsBlockRepository->delete([[
            'id' => $blockIdToDelete,
        ]], $versionContext);

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext, 5);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'delete', null, $details[0]);
        $this->assertDetail('cms_block', 'update', 'updated name', $details[1]);
        $this->assertDetail('cms_block', 'insert', null, $details[2]);
        $this->assertDetail('cms_block', 'insert', 'custom name 123', $details[3]);
        $this->assertDetail('cms_section', 'insert', 'section', $details[4]);
    }

    public function testWriteActivityCheckValidDateTime(): void
    {
        $context = $this->createAdminApiSourceContext();
        $page = $this->fetchCmsPage($context);
        $versionContext = $this->branchCmsPage($page->getId(), $context);

        $sectionId = $this->createSectionFixture('section', $page->getId(), $versionContext);
        $this->createBlockFixture($page->getId(), $sectionId, $versionContext, 'custom name 123');

        $details = $this->fetchActivityDetails($page->getId(), $versionContext);
        $detail = \array_pop($details);

        static::assertInstanceOf(
            \DateTime::class,
            \DateTime::createFromFormat(\DateTime::ATOM, $detail['timestamp'])
        );
    }

    public function testWriteActivityWithMultipleCmsElements(): void
    {
        $context = $this->createAdminApiSourceContext();
        $page = $this->fetchCmsPage($context);
        $versionContext = $this->branchCmsPage($page->getId(), $context);

        $sectionId = $this->createSectionFixture('section', $page->getId(), $versionContext);
        $blockId = $this->createBlockFixture($page->getId(), $sectionId, $versionContext, 'custom name 123');
        $this->createSlotFixture($blockId, $sectionId, $versionContext);

        $this->assertTotalActivitiesForPage($page->getId(), 1);
        $details = $this->fetchActivityDetails($page->getId(), $versionContext);
        static::assertCount(3, $details, \print_r($details, true));

        $this->assertDetail('cms_slot', 'insert', null, $details[0]);
        $this->assertDetail('cms_block', 'insert', 'custom name 123', $details[1]);
        $this->assertDetail('cms_section', 'insert', 'section', $details[2]);
    }

    public function testWriteActivitiesWithMultipleUsersInOneVersion(): void
    {
        $userId1 = $this->createUserFixture('user1@foo.com', '1');
        $context1 = $this->createAdminApiSourceContext($userId1);
        $userId2 = $this->createUserFixture('user2@foo.com', '2');
        $context2 = $this->createAdminApiSourceContext($userId2);
        $pageId = $this->createCmsPageFixture();
        $page = $this->fetchCmsPage($context1, $pageId);
        $versionContext1 = $this->branchCmsPage($page->getId(), $context1);
        $versionContext2 = $context2->createWithVersionId($versionContext1->getVersionId());

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 2);
        $details = $activity['details'];
        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        static::assertSame($userId1, $activity['userId']);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $blockId = $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');
        $this->createSlotFixture($blockId, $sectionId1, $versionContext1);

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 5);
        $details = $activity['details'];
        $this->assertDetail('cms_slot', 'insert', null, $details[0]);
        $this->assertDetail('cms_block', 'insert', 'user1', $details[1]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[2]);
        $this->assertDetail('cms_block', 'insert', 'user1', $details[3]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[4]);
        static::assertSame($userId1, $activity['userId']);
        $this->assertTotalActivitiesForPage($page->getId(), 1);

        $sectionId2 = $this->createSectionFixture('section2', $page->getId(), $versionContext2);
        $this->createBlockFixture($page->getId(), $sectionId2, $versionContext2, 'user2');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext2, 2);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user2', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section2', $details[1]);
        static::assertSame($userId2, $activity['userId']);
        $this->assertTotalActivitiesForPage($page->getId(), 2);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 2);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        $this->assertTotalActivitiesForPage($page->getId(), 3);
        static::assertSame($userId1, $activity['userId']);
    }

    public function testWriteActivitiesWithMultipleUsersInMultipleVersions(): void
    {
        $userId1 = $this->createUserFixture('user1@foo.com', '1');
        $context1 = $this->createAdminApiSourceContext($userId1);
        $pageId = $this->createCmsPageFixture();
        $page = $this->fetchCmsPage($context1, $pageId);
        $versionContext1 = $this->branchCmsPage($page->getId(), $context1);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 2);
        $details = $activity['details'];
        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        static::assertSame($userId1, $activity['userId']);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $blockId = $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');
        $this->createSlotFixture($blockId, $sectionId1, $versionContext1);

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 5);
        $details = $activity['details'];
        $this->assertDetail('cms_slot', 'insert', null, $details[0]);
        $this->assertDetail('cms_block', 'insert', 'user1', $details[1]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[2]);
        $this->assertDetail('cms_block', 'insert', 'user1', $details[3]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[4]);
        static::assertSame($userId1, $activity['userId']);
        $this->assertTotalActivitiesForPage($page->getId(), 1);

        $userId2 = $this->createUserFixture('user2@foo.com', '2');
        $context2 = $this->createAdminApiSourceContext($userId2);
        $versionContext2 = $this->branchCmsPage($page->getId(), $context2);

        $sectionId2 = $this->createSectionFixture('section2', $page->getId(), $versionContext2);
        $this->createBlockFixture($page->getId(), $sectionId2, $versionContext2, 'user2');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext2, 2);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user2', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section2', $details[1]);
        static::assertSame($userId2, $activity['userId']);
        $this->assertTotalActivitiesForPage($page->getId(), 2);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');

        $this->assertTotalActivitiesForPage($page->getId(), 2);
    }

    public function testWriteActivitiesForMultiplePages(): void
    {
        $userId = $this->createUserFixture('user@foo.com', 'user');
        $context = $this->createAdminApiSourceContext($userId);
        $page = $this->fetchCmsPage($context);
        $versionContext1 = $this->branchCmsPage($page->getId(), $context);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 2);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        static::assertSame($userId, $activity['userId']);

        $newPage = $this->fetchCmsPage($context, $this->createCmsPageFixture());
        $versionContext2 = $this->branchCmsPage($newPage->getId(), $context);

        $sectionId1 = $this->createSectionFixture('section1', $newPage->getId(), $versionContext2);
        $this->createBlockFixture($newPage->getId(), $sectionId1, $versionContext2, 'user1');

        $activity = $this->assertLatestActivityDetailCount($newPage->getId(), $versionContext2, 2);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        static::assertSame($userId, $activity['userId']);

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $versionContext1);
        $this->createBlockFixture($page->getId(), $sectionId1, $versionContext1, 'user1');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $versionContext1, 4);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        $this->assertDetail('cms_block', 'insert', 'user1', $details[2]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[3]);
        static::assertSame($userId, $activity['userId']);
    }

    public function testWriteNewDetailsIntoActivityWithoutExistingDetails(): void
    {
        $userId = $this->createUserFixture('user@foo.com', 'user');
        $context = $this->createAdminApiSourceContext($userId);
        $page = $this->fetchCmsPage($context, $this->createCmsPageFixture());

        $sectionId1 = $this->createSectionFixture('section1', $page->getId(), $context);
        $this->createBlockFixture($page->getId(), $sectionId1, $context, 'user1');

        $activity = $this->assertLatestActivityDetailCount($page->getId(), $context, 2);
        $details = $activity['details'];

        $this->assertDetail('cms_block', 'insert', 'user1', $details[0]);
        $this->assertDetail('cms_section', 'insert', 'section1', $details[1]);
        static::assertNull($activity['draftVersion']);
    }

    public function testUpdateParentCmsPageEntityByUpdatingTranslation(): void
    {
        $userId = $this->createUserFixture('user@foo.com', 'user');
        $context = $this->createAdminApiSourceContext($userId);

        $pageId = $this->importPage();

        /** @var DraftAction $draftAction */
        $draftAction = $this->getContainer()->get(DraftAction::class);

        $versionId = $draftAction->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        $name = 'super cool name 123';
        $this->updatePageTranslation([[
            'cmsPageId' => $pageId,
            'cmsPageVersionId' => $versionId,
            'name' => $name,
        ]], $versionContext);

        $details = $this->fetchActivityDetails($pageId, $versionContext)[0];

        static::assertSame($pageId, $details['id']);
        static::assertSame($name, $details['name']);
        static::assertSame(EntityWriteResult::OPERATION_UPDATE, $details['operation']);
        static::assertSame(CmsPageDefinition::ENTITY_NAME, $details['entityName']);
    }

    public function testUpdateParentCmsSlotEntityByUpdatingTranslationWithSameData(): void
    {
        $userId = $this->createUserFixture('user@foo.com', 'user');
        $context = $this->createAdminApiSourceContext($userId);

        $pageId = $this->importPage();

        /** @var DraftAction $draftAction */
        $draftAction = $this->getContainer()->get(DraftAction::class);

        $versionId = $draftAction->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        $sectionId = $this->createSectionFixture('section', $pageId, $versionContext);
        $blockId = $this->createBlockFixture($pageId, $sectionId, $versionContext, 'user1');
        $slotId = $this->createSlotFixture($blockId, $sectionId, $versionContext);

        $this->createSlotTranslation($slotId, $versionContext);
        $this->updateSlotTranslation([[
            'cmsSlotId' => $slotId,
            'cmsSlotVersionId' => $versionId,
            'config' => ['content' => ['source' => FieldConfig::SOURCE_STATIC, 'value' => 'foo']],
        ]], $versionContext);

        $details = $this->fetchActivityDetails($pageId, $versionContext);

        static::assertCount(3, $details);

        $slotInsert = $details[0];
        static::assertSame(EntityWriteResult::OPERATION_INSERT, $slotInsert['operation']);
        static::assertSame(CmsSlotDefinition::ENTITY_NAME, $slotInsert['entityName']);

        $blockInsert = $details[1];
        static::assertSame(EntityWriteResult::OPERATION_INSERT, $blockInsert['operation']);
        static::assertSame(CmsBlockDefinition::ENTITY_NAME, $blockInsert['entityName']);

        $sectionInsert = $details[2];
        static::assertSame(EntityWriteResult::OPERATION_INSERT, $sectionInsert['operation']);
        static::assertSame(CmsSectionDefinition::ENTITY_NAME, $sectionInsert['entityName']);
    }

    public function testUpdateParentCmsSlotEntityByUpdatingTranslationWithDifferentData(): void
    {
        $userId = $this->createUserFixture('user@foo.com', 'user');
        $context = $this->createAdminApiSourceContext($userId);

        $pageId = $this->importPage();

        /** @var DraftAction $draftAction */
        $draftAction = $this->getContainer()->get(DraftAction::class);

        $versionId = $draftAction->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        $sectionId = $this->createSectionFixture('section', $pageId, $versionContext);
        $blockId = $this->createBlockFixture($pageId, $sectionId, $versionContext, 'user1');
        $slotId = $this->createSlotFixture($blockId, $sectionId, $versionContext);

        $this->createSlotTranslation($slotId, $versionContext);
        $this->updateSlotTranslation([[
            'cmsSlotId' => $slotId,
            'cmsSlotVersionId' => $versionId,
            'config' => ['content' => ['source' => FieldConfig::SOURCE_STATIC, 'value' => 'bar']],
        ]], $versionContext);

        $details = $this->fetchActivityDetails($pageId, $versionContext);

        static::assertCount(4, $details);

        $slotUpdate = $details[0];
        static::assertSame(EntityWriteResult::OPERATION_UPDATE, $slotUpdate['operation']);
        static::assertSame(CmsSlotDefinition::ENTITY_NAME, $slotUpdate['entityName']);

        $slotInsert = $details[1];
        static::assertSame(EntityWriteResult::OPERATION_INSERT, $slotInsert['operation']);
        static::assertSame(CmsSlotDefinition::ENTITY_NAME, $slotInsert['entityName']);

        $blockInsert = $details[2];
        static::assertSame(EntityWriteResult::OPERATION_INSERT, $blockInsert['operation']);
        static::assertSame(CmsBlockDefinition::ENTITY_NAME, $blockInsert['entityName']);

        $sectionInsert = $details[3];
        static::assertSame(EntityWriteResult::OPERATION_INSERT, $sectionInsert['operation']);
        static::assertSame(CmsSectionDefinition::ENTITY_NAME, $sectionInsert['entityName']);
    }

    public function testTranslationInsertionWillNotBeLogged(): void
    {
        $userId = $this->createUserFixture('user@foo.com', 'user');
        $context = $this->createAdminApiSourceContext($userId);

        $pageId = $this->importPage();

        /** @var DraftAction $draftAction */
        $draftAction = $this->getContainer()->get(DraftAction::class);

        $versionId = $draftAction->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        $sectionId = $this->createSectionFixture('section', $pageId, $versionContext);
        $blockId = $this->createBlockFixture($pageId, $sectionId, $versionContext, 'user1');
        $slotId = $this->createSlotFixture($blockId, $sectionId, $versionContext);

        $this->createSlotTranslation($slotId, $versionContext);

        $details = $this->fetchActivityDetails($pageId, $versionContext);

        $cmsSlots = \array_filter($details, static function (array $detail) {
            return $detail['entityName'] === CmsSlotDefinition::ENTITY_NAME;
        });

        // 1 because of slot fixture creation
        static::assertCount(1, $cmsSlots);
    }

    public function testCreateNewActivityLogEntryByUpdatingDraftWithNewUser(): void
    {
        $userId1 = $this->createUserFixture('user1@foo.com', '1');
        $context1 = $this->createAdminApiSourceContext($userId1);

        $name = 'foo name 123';
        $pageId = $this->importPage($context1);

        /** @var DraftAction $draftAction */
        $draftAction = $this->getContainer()->get(DraftAction::class);
        $versionId = $draftAction->draft($pageId, $name, $context1);

        $activities = $this->getContainer()->get(VersionControlCmsGateway::class)
            ->searchActivities(CriteriaFactory::forActivityWithPageAndVersion($pageId, $versionId), $context1);

        static::assertSame(1, $activities->count());
        static::assertSame($name, $activities->first()->get('name'));

        $userId2 = $this->createUserFixture('user2@foo.com', '2');
        $context2 = $this->createAdminApiSourceContext($userId2);

        $this->createSectionFixture('test', $pageId, $context2->createWithVersionId($versionId));

        $criteria = CriteriaFactory::forActivityWithPageAndVersion($pageId, $versionId);
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::ASCENDING));

        $activities = $this->getContainer()->get(VersionControlCmsGateway::class)
            ->searchActivities($criteria, $context1)
            ->getElements();

        static::assertCount(2, $activities);

        $first = \array_shift($activities);
        self::assertActivity($name, $userId1, $versionId, $first);

        $second = \array_shift($activities);
        self::assertActivity($name, $userId2, $versionId, $second);

        static::assertNotSame($userId1, $userId2);
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame([
            CmsPageEvents::PAGE_WRITTEN_EVENT => 'logActivityOnCmsWriteEvent',
            CmsSectionDefinition::ENTITY_NAME . '.written' => 'logActivityOnCmsWriteEvent',
            CmsPageEvents::SLOT_WRITTEN_EVENT => 'logActivityOnCmsWriteEvent',
            CmsPageEvents::BLOCK_WRITTEN_EVENT => 'logActivityOnCmsWriteEvent',
            CmsSlotTranslationDefinition::ENTITY_NAME . '.written' => 'logActivityOnCmsWriteEvent',
            CmsPageTranslationDefinition::ENTITY_NAME . '.written' => 'logActivityOnCmsWriteEvent',
            EntityDeleteEvent::class => 'logActivityOnCmsDeleteEvent',
        ], ActivityLogSubscriber::getSubscribedEvents());
    }

    private static function assertActivity(
        string $name,
        string $userId,
        string $draftVersion,
        ArrayEntity $activity
    ): void {
        static::assertSame($name, $activity->get('name'));
        static::assertSame($userId, $activity->get('userId'));
        static::assertSame($draftVersion, $activity->get('draftVersion'));
    }

    private function createSlotTranslation(string $slotId, Context $versionContext): void
    {
        $this->getContainer()->get('cms_slot_translation.repository')->create([[
            'cmsSlotId' => $slotId,
            'cmsSlotVersionId' => $versionContext->getVersionId(),
            'config' => ['content' => ['source' => FieldConfig::SOURCE_STATIC, 'value' => 'foo']],
        ]], $versionContext);
    }

    private function updateSlotTranslation(array $data, Context $versionContext): void
    {
        $this->getContainer()->get('cms_slot_translation.repository')
            ->update($data, $versionContext);
    }

    private function updatePageTranslation(array $data, Context $versionContext): void
    {
        $this->getContainer()->get('cms_page_translation.repository')
            ->update($data, $versionContext);
    }

    private function fetchActivityDetails(string $pageId, Context $versionContext): array
    {
        $activityData = $this
            ->fetchCmsPage($versionContext, $pageId)
            ->getExtension('activities')
            ->first();

        static::assertInstanceOf(\DateTimeImmutable::class, $activityData['updatedAt']);
        static::assertSame(Defaults::LIVE_VERSION, $activityData['cmsPageVersionId']);
        static::assertSame($versionContext->getVersionId(), $activityData['draftVersion']);

        return $activityData['details'];
    }

    private function fetchLatestActivity(string $pageId, Context $context): ?ArrayEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('pageId', $pageId));
        $criteria->addSorting(new FieldSorting('createdAt', FieldSorting::DESCENDING));
        $criteria->setLimit(1);

        return $this->getCmsPageActivityRepository()
            ->search($criteria, $context->createWithVersionId(Defaults::LIVE_VERSION))
            ->first();
    }

    private function getCmsPageActivityRepository(): EntityRepository
    {
        return $this->getContainer()
            ->get('cms_page_activity.repository');
    }

    private function createBlockFixture(string $pageId, string $sectionId, Context $context, ?string $name = null): string
    {
        $id = Uuid::randomHex();
        $this->getCmsBlockRepository()->create([[
            'id' => $id,
            'sectionId' => $sectionId,
            'pageId' => $pageId,
            'type' => 'form',
            'position' => 0,
            'name' => $name,
        ]], $context);

        return $id;
    }

    private function deleteBlock(string $blockId, Context $context): void
    {
        $this->getContainer()->get('cms_block.repository')->delete([[
            'id' => $blockId,
        ]], $context);
    }

    private function createSlotFixture(string $blockId, string $sectionId, Context $context): string
    {
        $id = Uuid::randomHex();
        $this->getContainer()->get('cms_slot.repository')->create([[
            'id' => $id,
            'blockId' => $blockId,
            'sectionId' => $sectionId,
            'position' => 0,
            'type' => 'form',
            'slot' => 'right',
        ]], $context);

        return $id;
    }

    private function deleteSlot(string $slotId, Context $context): void
    {
        $this->getContainer()->get('cms_slot.repository')->delete([[
            'id' => $slotId,
        ]], $context);
    }

    private function getCmsBlockRepository(): EntityRepository
    {
        return $this->getContainer()->get('cms_block.repository');
    }

    private function createSectionFixture(string $name, string $pageId, Context $context): string
    {
        $id = Uuid::randomHex();
        $this->getCmsSectionRepository()->create([[
            'id' => $id,
            'name' => $name,
            'type' => 'default',
            'pageId' => $pageId,
            'versionId' => $context->getVersionId(),
            'sizingMode' => 'boxed',
            'mobileBehavior' => 'wrap',
            'position' => 2,
        ]], $context);

        return $id;
    }

    private function deleteSection(string $sectionId, Context $context): void
    {
        $this->getContainer()->get('cms_section.repository')->delete([[
            'id' => $sectionId,
        ]], $context);
    }

    private function getCmsSectionRepository(): EntityRepository
    {
        return $this->getContainer()
            ->get('cms_section.repository');
    }

    private function fetchCmsPage(Context $context, ?string $id = null): CmsPageEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locked', 0));
        $criteria->setLimit(1);
        $criteria->addAssociation('activities');
        $criteria->addAssociation('sections');

        if ($id) {
            $criteria->addFilter(new EqualsFilter('id', $id));
        }

        return $this->getCmsPageRepository()
            ->search($criteria, $context)
            ->first();
    }

    private function getCmsPageRepository(): EntityRepository
    {
        return $this->getContainer()
            ->get('cms_page.repository');
    }

    private function branchCmsPage(string $pageId, Context $context, bool $withDraft = true): Context
    {
        $context = $this->getContainer()
            ->get(VersionControlService::class)
            ->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);

        if ($withDraft) {
            $context->scope(Context::SYSTEM_SCOPE, function ($systemContext) use ($context, $pageId): void {
                $this->getContainer()->get(VersionControlCmsGateway::class)
                    ->createDrafts([[
                        'draftVersion' => $context->getVersionId(),
                        'pageId' => $pageId,
                        'name' => 'unit test',
                        'deepLinkCode' => Uuid::randomHex(),
                    ]], $systemContext);
            });
        }

        return $context;
    }

    private function createUserFixture(string $email, string $userName): string
    {
        $id = Uuid::randomHex();
        $data = [[
            'id' => $id,
            'email' => $email,
            'firstName' => 'Firstname',
            'lastName' => 'Lastname',
            'password' => 'password',
            'username' => $userName,
            'localeId' => $this->getContainer()->get(Connection::class)
                ->fetchOne('SELECT LOWER(HEX(id)) FROM locale LIMIT 1'),
        ]];

        $this->getContainer()
            ->get('user.repository')
            ->create($data, Context::createDefaultContext());

        return $id;
    }

    private function createCmsPageFixture(?Context $context = null): string
    {
        $id = Uuid::randomHex();
        $data = [[
            'id' => $id,
            'type' => 'page',
            'locked' => 0,
        ]];

        if (!$context) {
            $context = Context::createDefaultContext();
        }

        $this->getCmsPageRepository()
            ->create($data, $context);

        return $id;
    }

    private function assertTotalActivitiesForPage(string $pageId, int $expected): void
    {
        $count = (int) $this->getContainer()
            ->get(Connection::class)
            ->fetchOne('SELECT COUNT(*) FROM cms_page_activity WHERE cms_page_id=:pageId', ['pageId' => Uuid::fromHexToBytes($pageId)]);

        static::assertSame($expected, $count);
    }

    private function assertLatestActivityDetailCount(string $pageId, Context $context, int $expectedCount): ArrayEntity
    {
        $activity = $this->fetchLatestActivity($pageId, $context);
        static::assertNotNull($activity);
        static::assertCount($expectedCount, $activity['details'], \print_r($activity['details'], true));

        return $activity;
    }

    private function assertDetail(string $entityName, string $operation, ?string $name, array $detail): void
    {
        static::assertSame($entityName, $detail['entityName']);
        static::assertSame($operation, $detail['operation']);
        static::assertSame($name, $detail['name']);
    }

    private function fetchCategory(Context $context): CategoryEntity
    {
        return $this->getContainer()
            ->get('category.repository')
            ->search(new Criteria(), $context)
            ->first();
    }
}

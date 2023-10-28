<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteException;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\Data\ActivityCollection;
use SwagPublisher\VersionControlSystem\Data\DraftCollection;

class DataAccessTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function provideDefinitions(): array
    {
        return [
            ['cms_page_draft'],
            ['cms_page_activity'],
        ];
    }

    /**
     * @dataProvider provideDefinitions
     */
    public function testDefinitionEmpty(string $entityName): void
    {
        /** @var EntityRepository $repo */
        $repo = $this->getContainer()->get($entityName . '.repository');

        $result = $repo->search(new Criteria(), Context::createDefaultContext());

        static::assertSame(0, $result->count());
    }

    public function testDraftTableReadWriteDelete(): void
    {
        $repository = $this->getContainer()->get('cms_page_draft.repository');

        $pageId = $this->createPageFixture();
        $userId = $this->fetchUserId();
        $context = Context::createDefaultContext();

        $draftId = $this->createDraftFixture($pageId, $userId);

        static::assertSame(
            'draft-version',
            $repository->search(new Criteria(), $context)->first()['draftVersion']
        );

        $repository->update([[
            'id' => $draftId,
            'draftVersion' => 'foo',
        ]], $context);

        static::assertSame(
            'foo',
            $repository->search(new Criteria(), $context)->first()['draftVersion']
        );

        $repository->delete([['id' => $draftId]], $context);

        static::assertSame(
            0,
            $repository->search(new Criteria(), $context)->count()
        );
    }

    public function testDraftTableScopeIsImportant(): void
    {
        $repository = $this->getContainer()->get('cms_page_draft.repository');

        $pageId = $this->createPageFixture();
        $draftId = Uuid::randomHex();
        $userId = $this->fetchUserId();
        $context = Context::createDefaultContext(new AdminApiSource(null));

        $this->expectException(WriteException::class);
        $repository->create([[
            'id' => $draftId,
            'draftVersion' => 'draft-version',
            'pageId' => $pageId,
            'ownerId' => $userId,
        ]], $context);
    }

    public function testDraftPageCascade(): void
    {
        $pageId = $this->createPageFixture();
        $userId = $this->fetchUserId();

        $this->createDraftFixture($pageId, $userId);

        $adminSource = new AdminApiSource($this->fetchUserId());
        $adminSource->setPermissions(['cms_page:delete']);

        $this->getContainer()->get('cms_page.repository')->delete([['id' => $pageId]], Context::createDefaultContext($adminSource));
        static::markTestIncomplete('This should not be possible from a user scope');
    }

    public function testActivityReadWriteDelete(): void
    {
        $repository = $this->getContainer()->get('cms_page_activity.repository');

        $pageId = $this->createPageFixture();
        $userId = $this->fetchUserId();
        $context = Context::createDefaultContext();

        $activityId = $this->createActivityFixture($pageId, $userId);
        static::assertSame(
            'draft-version',
            $repository->search(new Criteria(), $context)->first()['draftVersion']
        );

        $repository->update([[
            'id' => $activityId,
            'draftVersion' => 'foo',
        ]], $context);

        static::assertSame(
            'foo',
            $repository->search(new Criteria(), $context)->first()['draftVersion']
        );
        static::assertSame(
            [['change', 'block']],
            $repository->search(new Criteria(), $context)->first()['details']
        );

        $repository->delete([['id' => $activityId]], $context);

        static::assertSame(
            0,
            $repository->search(new Criteria(), $context)->count()
        );
    }

    public function testActivityTableScopeIsImportant(): void
    {
        $repository = $this->getContainer()->get('cms_page_activity.repository');

        $pageId = $this->createPageFixture();
        $draftId = Uuid::randomHex();
        $context = Context::createDefaultContext(new AdminApiSource(null));

        $this->expectException(WriteException::class);
        $repository->create([[
            'id' => $draftId,
            'draftVersion' => 'draft-version',
            'pageId' => $pageId,
        ]], $context);
    }

    public function testActivityPageCascade(): void
    {
        $pageId = $this->createPageFixture();
        $this->createActivityFixture($pageId);

        $adminSource = new AdminApiSource($this->fetchUserId());
        $adminSource->setPermissions(['cms_page:delete']);

        $this->getContainer()->get('cms_page.repository')->delete([['id' => $pageId]], Context::createDefaultContext($adminSource));
        static::markTestIncomplete('This should not be possible from a user scope');
    }

    public function testPageAssociations(): void
    {
        $pageId = $this->createPageFixture();
        $draftId = $this->createDraftFixture($pageId);
        $activityId = $this->createActivityFixture($pageId);

        $criteria = new Criteria([$pageId]);
        $criteria->addAssociations(['activities', 'drafts']);

        /** @var CmsPageEntity $page */
        $page = $this->getContainer()->get('cms_page.repository')->search($criteria, Context::createDefaultContext())->first();

        static::assertSame($draftId, $page->getExtension('drafts')->first()['id']);
        static::assertSame($activityId, $page->getExtension('activities')->first()['id']);
    }

    public function testUserAssociations(): void
    {
        $pageId = $this->createPageFixture();
        $userId = $this->fetchUserId();
        $draftId = $this->createDraftFixture($pageId, $userId);
        $activityId = $this->createActivityFixture($pageId, $userId);

        $criteria = new Criteria([$userId]);
        $criteria->addAssociations(['cmsPageActivities', 'cmsPageDrafts']);

        /** @var CmsPageEntity $user */
        $user = $this->getContainer()->get('user.repository')->search($criteria, Context::createDefaultContext())->first();

        static::assertSame($draftId, $user->getExtension('cmsPageDrafts')->first()['id']);
        static::assertSame($activityId, $user->getExtension('cmsPageActivities')->first()['id']);
    }

    public function testCollectionClasses(): void
    {
        static::assertSame('cms_page_activity_collection', (new ActivityCollection())->getApiAlias());
        static::assertSame('cms_page_draft_collection', (new DraftCollection())->getApiAlias());
    }

    private function createPageFixture()
    {
        $pageId = Uuid::randomHex();

        $result = $this->getContainer()->get('cms_page.repository')->create(
            [['id' => $pageId, 'type' => 'foo']],
            Context::createDefaultContext()
        );

        static::assertSame([], $result->getErrors());

        return $pageId;
    }

    private function fetchUserId(): string
    {
        return $this->getContainer()
            ->get('user.repository')
            ->search(new Criteria(), Context::createDefaultContext())
            ->first()
            ->getId();
    }

    private function createActivityFixture(string $pageId, ?string $userId = null): string
    {
        $activityId = Uuid::randomHex();

        $this->getContainer()->get('cms_page_activity.repository')->create([[
            'id' => $activityId,
            'pageId' => $pageId,
            'userId' => $userId,
            'draftVersion' => 'draft-version',
            'details' => [
                ['change', 'block'],
            ],
            'isMerged' => false,
            'isDiscarded' => true,
            'name' => 'foo',
        ]], Context::createDefaultContext());

        return $activityId;
    }

    private function createDraftFixture(string $pageId, ?string $userId = null): string
    {
        $draftId = UuId::randomHex();

        $this->getContainer()->get('cms_page_draft.repository')->create([[
            'id' => $draftId,
            'draftVersion' => 'draft-version',
            'pageId' => $pageId,
            'ownerId' => $userId,
            'name' => 'foo',
            'deepLinkCode' => Uuid::randomHex(),
        ]], Context::createDefaultContext());

        return $draftId;
    }
}

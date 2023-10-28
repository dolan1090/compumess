<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Exception\NoDraftFound;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisher\VersionControlSystem\MergeAction;
use SwagPublisher\VersionControlSystem\UpdateFromLiveVersionAction;
use SwagPublisherTest\ComplexCmsPageTrait;
use SwagPublisherTest\ContextFactory;
use SwagPublisherTest\PublisherCmsFixtures;
use SwagPublisherTest\TestCaseBase\Cms\CmsTestTrait;

class UpdateFromLiveVersionActionTest extends TestCase
{
    use CmsTestTrait;
    use ComplexCmsPageTrait;
    use ContextFactory;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testThrowExceptionIfNoDraftFound(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        self::expectException(NoDraftFound::class);
        $this->getAction()->updateFromLiveVersion($pageId, Uuid::randomHex(), $context);
    }

    public function testUpdateFromLiveVersionAddsNewContent(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionId = $this->draftAction()
            ->draft($pageId, 'Draft', $context);

        $sectionId = Uuid::randomHex();
        $sectionName = 'Nice section';

        $this->createCmsSection($sectionId, $sectionName, $pageId, $context);

        $versionPage = $this->fetchFirstCmsPage($context->createWithVersionId($versionId));

        static::assertNotNull($versionPage);
        static::assertFalse(self::getSectionsFromPage($versionPage)->has($sectionId));

        $newVersionId = $this->getAction()
            ->updateFromLiveVersion($pageId, $versionId, $context);

        $versionPage = $this->fetchFirstCmsPage($context->createWithVersionId($newVersionId));
        $sections = self::getSectionsFromPage($versionPage);

        static::assertTrue($sections->has($sectionId));
        static::assertSame($sectionName, self::getSectionFromSections($sections, $sectionId)->getName());
    }

    public function testUpdateFromLiveVersionUpdatesExistingContent(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionId = $this->draftAction()
            ->draft($pageId, 'Draft', $context);

        $sectionRepository = $this->getCmsSectionRepository();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('pageId', $pageId));

        $sectionId = $sectionRepository
            ->searchIds($criteria, $context)->firstId();
        static::assertNotNull($sectionId);

        $sectionName = 'new name for our nice version';
        $sectionRepository->update([[
            'id' => $sectionId,
            'name' => $sectionName,
        ]], $context);

        $newVersionId = $this->getAction()
            ->updateFromLiveVersion($pageId, $versionId, $context);

        $versionPage = $this->fetchFirstCmsPage($context->createWithVersionId($newVersionId));
        $sections = self::getSectionsFromPage($versionPage);

        static::assertTrue($sections->has($sectionId));
        static::assertSame($sectionName, self::getSectionFromSections($sections, $sectionId)->getName());
    }

    public function testUpdateFromLiveVersionDeletesExistingContent(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionId = $this->draftAction()
            ->draft($pageId, 'Draft', $context);

        $sectionRepository = $this->getCmsSectionRepository();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('pageId', $pageId));

        $sectionId = $sectionRepository
            ->searchIds($criteria, $context)->firstId();

        $sectionRepository->delete([[
            'id' => $sectionId,
        ]], $context);

        $newVersionId = $this->getAction()
            ->updateFromLiveVersion($pageId, $versionId, $context);

        $versionPage = $this->fetchFirstCmsPage($context->createWithVersionId($newVersionId));
        $sections = self::getSectionsFromPage($versionPage);

        self::assertfalse($sections->has($sectionId));
    }

    public function testUpdateFromLiveVersionRemovesOriginalVersion(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionId = $this->draftAction()
            ->draft($pageId, 'Draft', $context);

        $this->getAction()
            ->updateFromLiveVersion($pageId, $versionId, $context);

        static::assertNull($this->fetchFirstCmsPageNullable($context->createWithVersionId($versionId)));
    }

    public function testUpdateFromLiveVersionUpdatesDraftVersionInDraftsAndActivities(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $draftName = 'Draft';
        $versionId = $this->draftAction()
            ->draft($pageId, $draftName, $context);

        $newVersionId = $this->getAction()
            ->updateFromLiveVersion($pageId, $versionId, $context);

        $drafts = $this->getCmsGateway()
            ->searchDrafts(CriteriaFactory::forDraftWithPageAndVersion($pageId, $newVersionId), $context);

        static::assertCount(1, $drafts);

        $draft = $drafts->first();
        static::assertInstanceOf(ArrayEntity::class, $draft);
        static::assertSame($draftName, $draft->get('name'));

        $activities = $this->getCmsGateway()
            ->searchDrafts(CriteriaFactory::forActivityWithPageAndVersion($pageId, $newVersionId), $context);

        static::assertCount(1, $activities);

        $activity = $activities->first();
        static::assertInstanceOf(ArrayEntity::class, $activity);
        static::assertSame($draftName, $activity->get('name'));
    }

    public function testUpdateFromLiveVersionWithComplexDataAndMerge(): void
    {
        $context = $this->createAdminApiSourceContext();
        $pageId = Uuid::randomHex();

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');
        static::assertInstanceOf(EntityRepository::class, $cmsPageRepository);

        $cmsPageRepository->create($this->getComplexCmsPageFixture($pageId), $context);

        $versionId = $this->draftAction()->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        $sectionRepository = $this->getCmsSectionRepository();

        $sectionFixture1 = $this->getSectionFixture();
        $sectionFixture1[0]['pageId'] = $pageId;

        $sectionFixture2 = $this->getSectionFixture();
        $sectionFixture2[0]['pageId'] = $pageId;

        $sectionRepository->create($sectionFixture1, $context);
        $sectionRepository->create($sectionFixture2, $versionContext);

        $newVersionId = $this->getAction()->updateFromLiveVersion($pageId, $versionId, $context);

        $mergeAction = $this->getContainer()->get(MergeAction::class);
        static::assertInstanceOf(MergeAction::class, $mergeAction);

        $mergeAction->merge($pageId, $newVersionId, $context);

        $criteria = new Criteria([$pageId]);
        $criteria->addAssociation('sections');

        $page = $cmsPageRepository
            ->search($criteria, $context)
            ->first();

        static::assertSame(3, $page->getSections()->count());
    }

    private function getConnection(): Connection
    {
        $connection = $this->getContainer()->get(Connection::class);

        static::assertInstanceOf(Connection::class, $connection);

        return $connection;
    }

    private function getAction(): UpdateFromLiveVersionAction
    {
        $updateFromLiveVersionAction = $this->getContainer()->get(UpdateFromLiveVersionAction::class);

        static::assertInstanceOf(UpdateFromLiveVersionAction::class, $updateFromLiveVersionAction);

        return $updateFromLiveVersionAction;
    }

    private function draftAction(): DraftAction
    {
        $draftAction = $this->getContainer()->get(DraftAction::class);

        static::assertInstanceOf(DraftAction::class, $draftAction);

        return $draftAction;
    }

    private function getCmsGateway(): VersionControlCmsGateway
    {
        $versionControlCmsGateway = $this->getContainer()->get(VersionControlCmsGateway::class);

        static::assertInstanceOf(VersionControlCmsGateway::class, $versionControlCmsGateway);

        return $versionControlCmsGateway;
    }

    private function fetchFirstCmsPage(Context $context): CmsPageEntity
    {
        $cmsPage = $this->fetchFirstCmsPageNullable($context);

        static::assertInstanceOf(CmsPageEntity::class, $cmsPage);

        return $cmsPage;
    }

    private function fetchFirstCmsPageNullable(Context $context): ?CmsPageEntity
    {
        $criteria = CriteriaFactory::forPageWithVersion($context->getVersionId());
        $criteria->addAssociation('sections');

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        static::assertInstanceOf(EntityRepository::class, $cmsPageRepository);

        return $cmsPageRepository->search($criteria, $context)->first();
    }

    private function createCmsSection(string $id, string $name, string $pageId, Context $context): void
    {
        $this->getCmsSectionRepository()
            ->create([[
                'id' => $id,
                'name' => $name,
                'type' => 'default',
                'pageId' => $pageId,
                'versionId' => $context->getVersionId(),
                'sizingMode' => 'boxed',
                'mobileBehavior' => 'wrap',
                'position' => 2,
                'blocks' => [
                    [
                        'id' => Uuid::randomHex(),
                        'position' => 1,
                        'section_position' => 'main',
                        'type' => 'form',
                        'name' => 'test form',
                        'locked' => 0,
                        'slots' => [[
                            'id' => Uuid::randomHex(),
                            'type' => 'form',
                            'slot' => 'content',
                            'translations' => [
                                Defaults::LANGUAGE_SYSTEM => ['config' => ['foo' => ['source' => FieldConfig::SOURCE_MAPPED, 'value' => 'en']]],
                            ],
                        ]],
                    ],
                ],
            ]], $context);
    }

    private function getCmsSectionRepository(): EntityRepository
    {
        $cmsSectionRepository = $this->getContainer()->get('cms_section.repository');

        static::assertInstanceOf(EntityRepository::class, $cmsSectionRepository);

        return $cmsSectionRepository;
    }
}

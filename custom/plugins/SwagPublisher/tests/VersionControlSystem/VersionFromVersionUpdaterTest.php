<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\DataResolver\FieldConfig;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use SwagPublisher\VersionControlSystem\Internal\VersionFromVersionUpdater;
use SwagPublisherTest\ContextFactory;
use SwagPublisherTest\PublisherCmsFixtures;
use SwagPublisherTest\TestCaseBase\Cms\CmsTestTrait;

class VersionFromVersionUpdaterTest extends TestCase
{
    use CmsTestTrait;
    use ContextFactory;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testUpdateFromVersionDoesNotRemoveNewContentInTargetVersion(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionContext = $this->branchEntity($pageId, $context);
        $tempVersionContext = $this->branchEntity($pageId, $context);

        $versionSectionId = Uuid::randomHex();
        $versionSectionName = 'Added new section into version page';

        $this->createCmsSection($versionSectionId, $versionSectionName, $pageId, $versionContext);

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);
        self::assertSectionsCountFromPage(5, $versionPage);

        $this->getVersionFromVersionUpdater()
            ->updateFromVersion($tempVersionContext->getVersionId(), WriteContext::createFromContext($versionContext));

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);
        self::assertSectionsCountFromPage(5, $versionPage);

        static::assertTrue($this->getSectionsFromPage($versionPage)->has($versionSectionId));
        static::assertSame($versionSectionName, $this->getSectionFromPage($versionPage, $versionSectionId)->getName());
    }

    public function testUpdateFromVersionDoesAddNewContentIntoTargetVersion(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionContext = $this->branchEntity($pageId, $context);
        $tempVersionContext = $this->branchEntity($pageId, $context);

        $tempVersionSectionId = Uuid::randomHex();
        $tempVersionSectionName = 'New content';

        $this->createCmsSection($tempVersionSectionId, $tempVersionSectionName, $pageId, $tempVersionContext);

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);
        self::assertSectionsCountFromPage(4, $versionPage);

        $this->getVersionFromVersionUpdater()
            ->updateFromVersion($tempVersionContext->getVersionId(), WriteContext::createFromContext($versionContext));

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);
        self::assertSectionsCountFromPage(5, $versionPage);

        static::assertTrue($this->getSectionsFromPage($versionPage)->has($tempVersionSectionId));
        self::assertSectionWithAssociations($tempVersionSectionName, $this->getSectionFromPage($versionPage, $tempVersionSectionId));
    }

    public function testUpdateFromVersionRemovesGivenVersion(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionContext = $this->branchEntity($pageId, $context);
        $tempVersionContext = $this->branchEntity($pageId, $context);

        $this->getVersionFromVersionUpdater()
            ->updateFromVersion($tempVersionContext->getVersionId(), WriteContext::createFromContext($versionContext));

        static::assertNull($this->fetchPageWithAssociationsNullable($pageId, $tempVersionContext));
    }

    public function testUpdateFromVersionRemovesInheritedContentFromTargetVersionButNotNewAdded(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionContext = $this->branchEntity($pageId, $context);
        $tempVersionContext = $this->branchEntity($pageId, $context);

        $versionSectionId = Uuid::randomHex();
        $versionSectionName = 'New content';

        $this->createCmsSection($versionSectionId, $versionSectionName, $pageId, $versionContext);

        $sectionRepository = $this->getCmsSectionRepository();

        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('pageId', $pageId),
            new EqualsFilter('versionId', $tempVersionContext->getVersionId()),
        ]));

        $sectionIdsToDelete = $sectionRepository
            ->searchIds($criteria, $tempVersionContext)
            ->getIds();

        $data = \array_map(static function (string $id) {
            return ['id' => $id];
        }, $sectionIdsToDelete);

        $sectionRepository->delete($data, $tempVersionContext);

        $tempVersionPage = $this->fetchPageWithAssociations($pageId, $tempVersionContext);
        self::assertSectionsCountFromPage(0, $tempVersionPage);

        $this->getVersionFromVersionUpdater()
            ->updateFromVersion($tempVersionContext->getVersionId(), WriteContext::createFromContext($versionContext));

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);
        self::assertSectionsCountFromPage(1, $versionPage);

        self::assertSectionWithAssociations($versionSectionName, $this->getSectionFromPage($versionPage, $versionSectionId));
    }

    public function testUpdateFromVersionUpdatesTranslations(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionContext = $this->branchEntity($pageId, $context);
        $tempVersionContext = $this->branchEntity($pageId, $context);

        $tempVersionSectionId = Uuid::randomHex();
        $this->createCmsSection($tempVersionSectionId, 'foo', $pageId, $tempVersionContext);

        $this->getVersionFromVersionUpdater()
            ->updateFromVersion($tempVersionContext->getVersionId(), WriteContext::createFromContext($versionContext));

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);

        $section = self::getSectionFromPage($versionPage, $tempVersionSectionId);
        $slots = self::getSlotsFromSection($section);

        $slot = $slots->first();
        static::assertNotNull($slot);

        $slotTranslations = self::getTranslationsFromSlot($slot);
        $slotTranslation = $slotTranslations->first();
        static::assertNotNull($slotTranslation);

        $config = $slotTranslation->getConfig();

        static::assertArrayHasKey('foo', $config);
        static::assertSame('en', $config['foo']['value']);
        static::assertSame('mapped', $config['foo']['source']);
    }

    public function testUpdateFromVersionUpdatesExistingContent(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();

        $versionContext = $this->branchEntity($pageId, $context);
        $tempVersionContext = $this->branchEntity($pageId, $context);

        $sectionRepository = $this->getCmsSectionRepository();

        $sectionId = $sectionRepository
            ->searchIds(new Criteria(), $tempVersionContext)
            ->firstId();
        static::assertNotNull($sectionId);

        $newName = 'New name 1234';
        $sectionRepository->update([[
            'id' => $sectionId,
            'versionId' => $tempVersionContext->getVersionId(),
            'name' => $newName,
        ]], $tempVersionContext);

        $this->getVersionFromVersionUpdater()
            ->updateFromVersion($tempVersionContext->getVersionId(), WriteContext::createFromContext($versionContext));

        $versionPage = $this->fetchPageWithAssociations($pageId, $versionContext);

        $section = self::getSectionFromPage($versionPage, $sectionId);
        static::assertSame($newName, $section->getName());
    }

    private static function assertSectionWithAssociations(string $sectionName, CmsSectionEntity $section): void
    {
        static::assertSame($sectionName, $section->getName());
        self::assertBlocksCountFromSection(1, $section);

        $blocks = self::getBlocksFromSection($section);
        $block = $blocks->first();
        static::assertNotNull($block);

        static::assertSame(1, $block->getPosition());
        static::assertSame('main', $block->getSectionPosition());
        static::assertSame('form', $block->getType());
        self::assertSlotsCountFromBlock(1, $block);

        $slots = self::getSlotsFromBlock($block);
        $slot = $slots->first();
        static::assertNotNull($slot);
        static::assertSame('form', $slot->getType());
        static::assertSame('content', $slot->getSlot());
    }

    private function fetchPageWithAssociations(string $pageId, Context $context): CmsPageEntity
    {
        /** @var CmsPageEntity $page */
        $page = $this->fetchPageWithAssociationsNullable($pageId, $context);

        static::assertNotNull($page);

        return $page;
    }

    private function fetchPageWithAssociationsNullable(string $pageId, Context $context): ?CmsPageEntity
    {
        $criteria = new Criteria([$pageId]);
        $criteria->addFilter(new EqualsFilter('versionId', $context->getVersionId()));
        $criteria->addAssociation('sections');
        $criteria->addAssociation('sections.blocks');
        $criteria->addAssociation('sections.blocks.slots');
        $criteria->addAssociation('sections.blocks.slots.translations');

        return $this->getCmsPageRepository()
            ->search($criteria, $context)
            ->first();
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

    private function getCmsPageRepository(): EntityRepository
    {
        /** @var EntityRepository $cmsPageRepository */
        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        static::assertInstanceOf(EntityRepository::class, $cmsPageRepository);

        return $cmsPageRepository;
    }

    private function getCmsSectionRepository(): EntityRepository
    {
        /** @var EntityRepository $cmsSectionRepository */
        $cmsSectionRepository = $this->getContainer()->get('cms_section.repository');

        static::assertInstanceOf(EntityRepository::class, $cmsSectionRepository);

        return $cmsSectionRepository;
    }

    private function getVersionFromVersionUpdater(): VersionFromVersionUpdater
    {
        /** @var VersionFromVersionUpdater $versionFromUpdater */
        $versionFromUpdater = $this->getContainer()->get(VersionFromVersionUpdater::class);

        static::assertInstanceOf(VersionFromVersionUpdater::class, $versionFromUpdater);

        return $versionFromUpdater;
    }

    private function branchEntity(string $pageId, Context $context): Context
    {
        /** @var VersionControlService $versionControlService */
        $versionControlService = $this->getContainer()->get(VersionControlService::class);

        static::assertInstanceOf(VersionControlService::class, $versionControlService);

        return $versionControlService->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);
    }
}

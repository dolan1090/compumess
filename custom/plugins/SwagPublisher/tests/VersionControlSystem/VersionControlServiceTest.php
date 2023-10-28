<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Version\VersionEntity;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\Exception\NotFoundException;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use SwagPublisherTest\ContextFactory;
use SwagPublisherTest\PublisherCmsFixtures;
use SwagPublisherTest\TestCaseBase\Cms\CmsTestTrait;

class VersionControlServiceTest extends TestCase
{
    use CmsTestTrait;
    use ContextFactory;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    private const CMS_FORBIDDEN_CHECK_KEYS = [
        'id', 'versionId', 'cmsPageVersionId', 'cmsSlotVersionId', '_uniqueIdentifier',
        'cmsSectionVersionId', 'sectionId', 'cmsBlockVersionId', 'cmsSlotId', 'backgroundMediaId',
        'cmsPageId', 'previewMediaId', 'pageId', 'createdAt',
    ];

    public function testCommitsHaveSameVersions(): void
    {
        $context = Context::createDefaultContext();
        $newContext = $this->getVersionControlService()
            ->branch(
                $this->fetchCmsPageId($context),
                CmsPageDefinition::ENTITY_NAME,
                $context
            );

        static::assertNotSame($context, $newContext);

        $commits = $this->fetchVersionEntity($newContext)
            ->getCommits();

        foreach ($commits as $commit) {
            static::assertSame($newContext->getVersionId(), $commit->getVersionId());
        }
    }

    public function testCreateMultipleBranchesAndCheckVersions(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();
        $cmsPageId = $this->fetchCmsPageId($context);

        $contextV1 = $service->branch(
            $cmsPageId,
            CmsPageDefinition::ENTITY_NAME,
            $context
        );

        $cmsPageV1 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV1);
        self::assertVersions($context, $contextV1, $cmsPageV1);

        $contextV2 = $service->branch(
            $this->fetchCmsPageId($contextV1),
            CmsPageDefinition::ENTITY_NAME,
            $contextV1
        );

        $cmsPageV2 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV2);
        self::assertVersions($contextV1, $contextV2, $cmsPageV2);

        $this->getCmsPageRepository()->update([[
            'id' => $cmsPageId,
            'type' => 'product_list',
        ]], $contextV2);

        $cmsPageV1 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV1);
        static::assertSame('page', $cmsPageV1->getType());

        $cmsPageV2 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV2);
        static::assertSame('product_list', $cmsPageV2->getType());
    }

    public function testExtendBranchAndMergeResultIntoRoot(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();
        $cmsPageId = $this->fetchCmsPageId($context);

        $contextV1 = $service->branch(
            $cmsPageId,
            CmsPageDefinition::ENTITY_NAME,
            $context
        );

        $cmsSectionId = Uuid::randomHex();
        $cmsSectionName = 'Super cool cms section';

        $this->createCmsSection($cmsSectionId, $cmsSectionName, $cmsPageId, $contextV1);

        $cmsPage = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $context);
        static::assertNull(self::getSectionFromPageNullable($cmsPage, $cmsSectionId));

        $cmsPageV1 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV1);
        static::assertSame($cmsSectionName, self::getSectionFromPage($cmsPageV1, $cmsSectionId)->getName());

        $service->merge($contextV1->getVersionId(), CmsPageDefinition::ENTITY_NAME, $context);

        $cmsPage = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $context);
        static::assertSame($cmsSectionName, self::getSectionFromPage($cmsPage, $cmsSectionId)->getName());
    }

    public function testMergeMultipleBranchesIntoRoot(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();
        $cmsPageId = $this->fetchCmsPageId($context);

        $contextV1 = $service->branch(
            $cmsPageId,
            CmsPageDefinition::ENTITY_NAME,
            $context
        );

        $contextV2 = $service->branch(
            $cmsPageId,
            CmsPageDefinition::ENTITY_NAME,
            $contextV1
        );

        $cmsSectionIdV1 = Uuid::randomHex();
        $cmsSectionNameV1 = 'cms section for the first version';

        $cmsSectionIdV2 = Uuid::randomHex();
        $cmsSectionNameV2 = 'new cms section v2';

        $this->createCmsSection($cmsSectionIdV1, $cmsSectionNameV1, $cmsPageId, $contextV1);
        $this->createCmsSection($cmsSectionIdV2, $cmsSectionNameV2, $cmsPageId, $contextV2);

        $cmsPageV1 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV1);
        static::assertSame($cmsSectionNameV1, self::getSectionFromPage($cmsPageV1, $cmsSectionIdV1)->getName());
        static::assertNull(self::getSectionFromPageNullable($cmsPageV1, $cmsSectionIdV2));

        $cmsPageV2 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV2);
        static::assertSame($cmsSectionNameV2, self::getSectionFromPage($cmsPageV2, $cmsSectionIdV2)->getName());
        static::assertNull(self::getSectionFromPageNullable($cmsPageV2, $cmsSectionIdV1));

        $cmsPageOriginal = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $context);
        static::assertNull(self::getSectionFromPageNullable($cmsPageOriginal, $cmsSectionIdV1));
        static::assertNull(self::getSectionFromPageNullable($cmsPageOriginal, $cmsSectionIdV2));

        $service->merge($contextV1->getVersionId(), CmsPageDefinition::ENTITY_NAME, $context);
        $service->merge($contextV2->getVersionId(), CmsPageDefinition::ENTITY_NAME, $context);

        $cmsPageOriginal = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $context);
        static::assertSame($cmsSectionNameV1, self::getSectionFromPage($cmsPageOriginal, $cmsSectionIdV1)->getName());
        static::assertSame($cmsSectionNameV2, self::getSectionFromPage($cmsPageOriginal, $cmsSectionIdV2)->getName());
    }

    public function testReleaseAsNewBranchedEntityWithAssociations(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();
        $cmsPageId = $this->fetchCmsPageId($context);

        $contextV1 = $service->branch(
            $cmsPageId,
            CmsPageDefinition::ENTITY_NAME,
            $context
        );

        $cmsSectionId = Uuid::randomHex();
        $cmsSectionName = 'New cms section';

        $this->createCmsSection($cmsSectionId, $cmsSectionName, $cmsPageId, $contextV1);

        $releasedCmsPageId = $service->releaseAsNew($cmsPageId, CmsPageDefinition::ENTITY_NAME, $contextV1);
        $releasedCmsPage = $this->fetchCmsPage($this->createCmsCriteria($releasedCmsPageId), $context);

        static::assertNotSame($cmsPageId, $releasedCmsPageId);
        static::assertSame($context->getVersionId(), $releasedCmsPage->getVersionId());
        self::assertSectionsCountFromPage(2, $releasedCmsPage);

        $hasName = false;
        foreach (self::getSectionsFromPage($releasedCmsPage)->getElements() as $sectionEntity) {
            if ($sectionEntity->getName() !== $cmsSectionName) {
                continue;
            }
            $hasName = true;
        }

        static::assertTrue($hasName);

        $cmsPageOriginal = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $context);
        self::assertSectionsCountFromPage(1, $cmsPageOriginal);

        $cmsPageV1 = $this->fetchCmsPage($this->createCmsCriteria($cmsPageId), $contextV1);
        static::assertInstanceOf(CmsPageEntity::class, $cmsPageV1);
    }

    public function testReleaseAsNewWithTranslationsAndCheckNames(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();
        $cmsPageId = $this->fetchCmsPageId($context);

        $criteria = new Criteria([$cmsPageId]);
        $criteria->addAssociation('translations');

        $cmsPageOriginal = $this->fetchCmsPage($criteria, $context);

        $translations = [];
        $translationCollection = $cmsPageOriginal->getTranslations();
        static::assertNotNull($translationCollection);

        foreach ($translationCollection->getElements() as $translation) {
            $translations[$translation->getLanguageId()] = $translation->getName();
        }

        $releasedCmsPageId = $service->releaseAsNew($cmsPageId, CmsPageDefinition::ENTITY_NAME, $context);

        $criteria = new Criteria([$releasedCmsPageId]);
        $criteria->addAssociation('translations');

        $releasedCmsPage = $this->fetchCmsPage($criteria, $context);
        $translationCollection = $releasedCmsPage->getTranslations();
        static::assertNotNull($translationCollection);

        foreach ($translationCollection->getElements() as $key => $translation) {
            static::assertSame($translations[$translation->getLanguageId()], $translation->getName());
        }
    }

    public function testReleaseAsNewAndCheckContent(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();
        $cmsPageId = $this->fetchCmsPageId($context);

        $releasedCmsPageId = $service->releaseAsNew($cmsPageId, CmsPageDefinition::ENTITY_NAME, $context);
        $releasedCmsPage = $this->fetchCmsPage($this->createCmsCriteria($releasedCmsPageId), $context);

        $sections = self::getSectionsFromPage($releasedCmsPage);
        $section = $sections->first();

        static::assertNotNull($section);
        static::assertSame('default', $section->getType());
        static::assertSame($releasedCmsPageId, $section->getPageId());
        static::assertNotSame($cmsPageId, $releasedCmsPageId);
        static::assertSame('boxed', $section->getSizingMode());
        static::assertSame(Defaults::LIVE_VERSION, $section->getVersionId());
    }

    public function testReleaseAsNewAssertRecursiveCreatedArrays(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();

        $cmsPageId = $this->fetchCmsPageId($context);
        $originalCriteria = $this->createBaseCriteria();
        $originalCriteria->addFilter(new EqualsFilter('id', $cmsPageId));

        $cmsPage = $this->fetchCmsPage($originalCriteria, $context);

        $releasedCmsPageId = $service->releaseAsNew($cmsPageId, CmsPageDefinition::ENTITY_NAME, $context);
        $releaseCriteria = $this->createBaseCriteria();
        $releaseCriteria->addFilter(new EqualsFilter('id', $releasedCmsPageId));

        $releasedCmsPage = $this->fetchCmsPage($releaseCriteria, $context);

        $serialized = $this->serializeCmsPage($cmsPage);
        $releaseSerialized = $this->serializeCmsPage($releasedCmsPage);

        static::assertEquals($serialized, $releaseSerialized);
    }

    public function testReleaseAsNewThrowsExceptionIfNoValidIdGiven(): void
    {
        self::expectException(NotFoundException::class);
        self::expectExceptionMessage('Entity with given id and version could not be found');

        $this->getVersionControlService()->releaseAsNew(
            Uuid::randomHex(),
            CmsPageDefinition::ENTITY_NAME,
            Context::createDefaultContext()
        );
    }

    public function testDiscardHandlesRemovalOfVersionEntities(): void
    {
        $service = $this->getVersionControlService();
        $context = Context::createDefaultContext();

        $cmsPageId = $this->fetchCmsPageId($context);
        $criteria = new Criteria([$cmsPageId]);

        $versionContext = $service->branch($cmsPageId, CmsPageDefinition::ENTITY_NAME, $context);

        $result = $this->getCmsPageRepository()->update([[
            'id' => $cmsPageId,
            'name' => 'foo',
        ]], $versionContext);
        static::assertCount(0, $result->getErrors());

        static::markTestSkipped('Translations seem incompatibel with versions');
        /** @phpstan-ignore-next-line */
        static::assertSame(1, $this->getCmsPageRepository()->search($criteria, $context)->count());
        static::assertNotSame('foo', $this->fetchCmsPage($criteria, $context)->getName());
        static::assertSame(1, $this->getCmsPageRepository()->search($criteria, $versionContext)->count());
        static::assertSame('foo', $this->fetchCmsPage($criteria, $versionContext)->getName());

        $service->discard($cmsPageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        static::assertSame(1, $this->getCmsPageRepository()->search($criteria, $context)->count());
        static::assertSame(0, $this->getCmsPageRepository()->search($criteria, $versionContext)->count());
    }

    public function testDuplicateCreatesDraftRelativeToTheExistingPageWithNewVersion(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        $versionContext = $this->getVersionControlService()
            ->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);

        $this->assertPageTypes($pageId, [
            [$context, 'page'],
            [$versionContext, 'page'],
        ]);

        $this->updatePageType($pageId, 'foo', $versionContext);

        $newVersionContext = $this->getVersionControlService()
            ->duplicate($pageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        $this->assertPageTypes($pageId, [
            [$context, 'page'],
            [$versionContext, 'foo'],
            [$newVersionContext, 'foo'],
        ]);

        $this->updatePageType($pageId, 'bar', $newVersionContext);

        static::assertNotSame($newVersionContext->getVersionId(), $versionContext->getVersionId());
        $this->assertPageTypes($pageId, [
            [$context, 'page'],
            [$versionContext, 'foo'],
            [$newVersionContext, 'bar'],
        ]);
    }

    public function testDuplicateDoesNotRemoveOriginalPageAndVersion(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $service = $this->getVersionControlService();

        $versionContext = $service->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);
        $service->duplicate($pageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        $cmsPageRepository = $this->getCmsPageRepository();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('versionId', $versionContext->getVersionId()));

        $originalVersionPage = $cmsPageRepository
            ->search($criteria, $versionContext);

        static::assertNotNull($originalVersionPage);
        static::assertCount(1, $originalVersionPage);

        $originalPage = $cmsPageRepository
            ->search(new Criteria([$originalVersionPage->first()->getId()]), $context);

        static::assertNotNull($originalPage);
        static::assertCount(1, $originalPage);
    }

    public function testDuplicateAssertRecursiveCreatedArrays(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        $versionContext = $this->getVersionControlService()
            ->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);

        $newVersionContext = $this->getVersionControlService()
            ->duplicate($pageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        $cmsPageRepository = $this->getCmsPageRepository();

        $newVersionPage = $cmsPageRepository
            ->search(CriteriaFactory::forPageWithVersion($newVersionContext->getVersionId()), $newVersionContext)
            ->first();

        $originalVersionPage = $cmsPageRepository
            ->search(CriteriaFactory::forPageWithVersion($versionContext->getVersionId()), $versionContext)
            ->first();

        $newSerialized = $this->serializeCmsPage($newVersionPage);
        $originalSerialized = $this->serializeCmsPage($originalVersionPage);

        static::assertEquals($originalSerialized, $newSerialized);
    }

    public function testCheckAssociationFieldsOfCmsDefinitions(): void
    {
        $cmsPageRepository = $this->getCmsPageRepository();

        self::validateDefinitions($cmsPageRepository->getDefinition());
    }

    public function testUpdateFromLiveVersion(): void
    {
        $pageId = $this->importPage();
        $context = $this->createAdminApiSourceContext();
        $versionService = $this->getVersionControlService();

        $versionContext = $versionService
            ->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);

        $liveSectionId = Uuid::randomHex();
        $versionSectionId = Uuid::randomHex();

        $liveName = 'live';
        $versionName = 'version';

        $this->createCmsSection($liveSectionId, $liveName, $pageId, $context);
        $this->createCmsSection($versionSectionId, $versionName, $pageId, $versionContext);

        $newVersionId = $versionService
            ->updateFromLiveVersion($pageId, CmsPageDefinition::ENTITY_NAME, $versionContext);

        $criteria = CriteriaFactory::forPageWithVersion($newVersionId);
        $criteria->addAssociation('sections');

        $versionPage = $this->getCmsPageRepository()
            ->search($criteria, $versionContext->createWithVersionId($newVersionId))
            ->first();

        $sections = $versionPage->getSections();
        static::assertSame(6, $sections->count());

        static::assertTrue($sections->has($versionSectionId));
        static::assertSame($versionName, $sections->get($versionSectionId)->getName());

        static::assertTrue($sections->has($liveSectionId));
        static::assertSame($liveName, $sections->get($liveSectionId)->getName());
    }

    public function testDuplicateThrowsExceptionIfNoValidIdGiven(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->getVersionControlService()->duplicate(
            Uuid::randomHex(),
            CmsPageDefinition::ENTITY_NAME,
            Context::createDefaultContext()
        );
    }

    private function getVersionControlService(): VersionControlService
    {
        $versionControlService = $this->getContainer()->get(VersionControlService::class);

        static::assertInstanceOf(VersionControlService::class, $versionControlService);

        return $versionControlService;
    }

    private function validateDefinitions(EntityDefinition $definition): void
    {
        $cascades = $definition->getFields()->filter(static function (Field $field) {
            /** @var CascadeDelete|null $flag */
            $flag = $field->getFlag(CascadeDelete::class);

            return $flag ? $flag->isCloneRelevant() : false;
        });

        /** @var AssociationField $cascade */
        foreach ($cascades as $cascade) {
            $reference = $cascade->getReferenceDefinition();
            $childrenAware = $reference->isChildrenAware();

            if ($childrenAware && $reference !== $definition) {
                static::fail();
            }

            if (self::isWrongInstanceOf($cascade)) {
                static::fail();
            }

            $this->validateDefinitions($cascade->getReferenceDefinition());
        }
    }

    private static function isWrongInstanceOf(Field $field): bool
    {
        return $field instanceof ManyToManyAssociationField
            || $field instanceof ManyToOneAssociationField
            || $field instanceof ChildrenAssociationField;
    }

    private function createBaseCriteria(): Criteria
    {
        $baseCriteria = new Criteria();
        $baseCriteria->addAssociation('sections');
        $baseCriteria->addAssociation('translations');
        $baseCriteria->getAssociation('sections')
            ->addAssociation('blocks');

        return $baseCriteria;
    }

    private function serializeCmsPage(CmsPageEntity $entity): array
    {
        $data = $this->serializeData([$entity->jsonSerialize()]);

        return \array_shift($data);
    }

    private function serializeData(array $data): array
    {
        $result = [];
        foreach ($data as $key => $datum) {
            if (\in_array($key, self::CMS_FORBIDDEN_CHECK_KEYS, true)) {
                unset($data[$key]);

                continue;
            }

            $actual = $datum;
            if ($datum instanceof Struct) {
                $actual = $this->serializeData($datum->jsonSerialize());
            } elseif (\is_array($datum)) {
                $actual = $this->serializeData($datum);
            }

            $result[$key] = $actual;
        }

        return $result;
    }

    private static function assertVersions(Context $old, Context $new, CmsPageEntity $cmsPage): void
    {
        static::assertSame($new->getVersionId(), $cmsPage->getVersionId());
        static::assertNotSame($old->getVersionId(), $new->getVersionId());
    }

    private function fetchCmsPageId(Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locked', 0));
        $criteria->setLimit(1);

        return $this->fetchCmsPage($criteria, $context)->getId();
    }

    private function createCmsCriteria(string $id): Criteria
    {
        $criteria = new Criteria([$id]);
        $criteria->addAssociation('sections');

        return $criteria;
    }

    private function fetchCmsPage(Criteria $criteria, Context $context): CmsPageEntity
    {
        return $this->getCmsPageRepository()
            ->search($criteria, $context)
            ->first();
    }

    private function getCmsPageRepository(): EntityRepository
    {
        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        static::assertInstanceOf(EntityRepository::class, $cmsPageRepository);

        return $cmsPageRepository;
    }

    private function fetchVersionEntity(Context $context): VersionEntity
    {
        $criteria = new Criteria();
        $criteria->addAssociation('commits');
        $criteria->addAssociation('commits.data');

        $versionRepository = $this->getContainer()->get('version.repository');

        static::assertInstanceOf(EntityRepository::class, $versionRepository);

        return $versionRepository
            ->search($criteria, $context)
            ->first();
    }

    private function createCmsSection(string $id, string $name, string $pageId, Context $context): void
    {
        $cmsSectionRepository = $this->getContainer()->get('cms_section.repository');

        static::assertInstanceOf(EntityRepository::class, $cmsSectionRepository);

        $cmsSectionRepository->create([[
            'id' => $id,
            'name' => $name,
            'type' => 'default',
            'pageId' => $pageId,
            'versionId' => $context->getVersionId(),
            'sizingMode' => 'boxed',
            'mobileBehavior' => 'wrap',
            'position' => 2,
        ]], $context);
    }

    private function assertPageTypes(string $pageId, array $expectations): void
    {
        $cmsPageRepository = $this->getCmsPageRepository();

        foreach ($expectations as [$context, $expectedType]) {
            $duplicatedVersionPage = $cmsPageRepository
                ->search(new Criteria([$pageId]), $context);

            static::assertCount(1, $duplicatedVersionPage);

            $page = $duplicatedVersionPage->first();
            static::assertSame($page->getType(), $expectedType);
        }
    }
}

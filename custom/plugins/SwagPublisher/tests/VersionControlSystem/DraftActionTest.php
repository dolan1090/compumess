<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisherTest\DraftIntegrationTrait;
use SwagPublisherTest\PublisherCmsFixtures;

class DraftActionTest extends TestCase
{
    use DraftIntegrationTrait;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    private const DRAFT_EXTENSION = 'drafts';

    public function testItWritesNames(): void
    {
        $pageId = $this->importPage();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $context = Context::createDefaultContext();

        $versionId = $action->draft($pageId, 'foo', $context);

        $result = $this->getContainer()->get('cms_page_draft.repository')
            ->search(new Criteria(), $context->createWithVersionId($versionId));

        static::assertSame(1, $result->count());
        static::assertSame('foo', $result->first()['name']);
    }

    public function testItWritesNamesFromRequest(): void
    {
        $pageId = $this->importPage();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $context = Context::createDefaultContext();

        $versionId = $action->onDraft($pageId, new RequestDataBag(['name' => 'foo']), $context)->getContent();

        $result = $this->getContainer()->get('cms_page_draft.repository')
            ->search(new Criteria(), $context->createWithVersionId($versionId));

        static::assertSame(1, $result->count());
        static::assertSame('foo', $result->first()['name']);
    }

    public function testItWritesDefaultNamesFromRequest(): void
    {
        $pageId = $this->importPage();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $context = Context::createDefaultContext();

        $versionId = $action->onDraft($pageId, new RequestDataBag(), $context)->getContent();

        $result = $this->getContainer()->get('cms_page_draft.repository')
            ->search(new Criteria(), $context->createWithVersionId($versionId));

        static::assertSame(1, $result->count());
        static::assertSame('enterprise', $result->first()['name']);
    }

    public function testItDefaultsIfPageHasNoName(): void
    {
        $pageFixture = $this->getCmsPageFixture();
        unset($pageFixture[0]['name']);
        $this->getContainer()
            ->get('cms_page.repository')
            ->create($pageFixture, Context::createDefaultContext());
        $pageId = $pageFixture[0]['id'];

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $context = Context::createDefaultContext();

        $versionId = $action->onDraft($pageId, new RequestDataBag(), $context)->getContent();

        $result = $this->getContainer()->get('cms_page_draft.repository')
            ->search(new Criteria(), $context->createWithVersionId($versionId));

        static::assertSame(1, $result->count());
        static::assertSame('-', $result->first()['name']);
    }

    public function testCreateDraftWithExistingPreviewMediaId(): void
    {
        $pageId = $this->importPage();
        $mediaId = $this->fetchFirstMediaId();
        $context = Context::createDefaultContext();

        $this->updatePreviewMediaId($pageId, $mediaId, $context);

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->onDraft($pageId, new RequestDataBag(), $context)->getContent();
        $versionContext = $context->createWithVersionId(\trim($versionId, '"'));

        $this->assertPreviewMedia($pageId, $mediaId, $versionContext);
    }

    public function testCreateDraftWithoutExistingPreviewMediaId(): void
    {
        $pageId = $this->importPage();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $context = Context::createDefaultContext();

        $versionId = $action->onDraft($pageId, new RequestDataBag(), $context)->getContent();
        $versionContext = $context->createWithVersionId(\trim($versionId, '"'));

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $originalPage = $cmsPageRepository
            ->search(new Criteria([$pageId]), $context)
            ->first();

        static::assertNull($originalPage->getPreviewMediaId());

        $versionPage = $cmsPageRepository
            ->search(self::createVersionCriteria($pageId), $versionContext)
            ->first();

        static::assertNull($versionPage->getPreviewMediaId());
        static::assertNull($versionPage->getExtension(self::DRAFT_EXTENSION)->get('previewMediaId'));
    }

    public function testCreateAndUpdateDraftWithMediaIdWithoutUpdatingOriginal(): void
    {
        $pageId = $this->importPage();
        $originalMediaId = $this->fetchFirstMediaId();
        $context = Context::createDefaultContext();

        $this->updatePreviewMediaId($pageId, $originalMediaId, $context);

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->onDraft($pageId, new RequestDataBag(), $context)->getContent();
        $versionContext = $context->createWithVersionId(\trim($versionId, '"'));

        $draft = $this->getContainer()->get('cms_page_draft.repository')
            ->search(new Criteria(), $versionContext)
            ->first();

        static::assertSame($originalMediaId, $draft['previewMediaId']);

        $newMediaId = $this->fetchAnotherMediaIdAsOriginal($originalMediaId, $context);

        static::assertNotSame($originalMediaId, $newMediaId);

        $this->updatePreviewMediaId($pageId, $newMediaId, $versionContext);

        $this->assertPreviewMedia($pageId, $originalMediaId, $versionContext, $newMediaId);
    }

    public function testDraftActionWillNotAddDetailsIntoActivityLog(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->onDraft($pageId, new RequestDataBag(), $context)->getContent();
        $versionContext = $context->createWithVersionId(\trim($versionId, '"'));

        /** @var VersionControlCmsGateway $cmsGateway */
        $cmsGateway = $this->getContainer()->get(VersionControlCmsGateway::class);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('pageId', $pageId));

        $activities = $cmsGateway
            ->searchActivities($criteria, $versionContext)
            ->first();

        static::assertEmpty($activities->get('details'));
    }

    public function testDraftActionSavesNameInActivityLog(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $name = 'foo';
        $versionId = $action->draft($pageId, $name, $context);
        $versionContext = $context->createWithVersionId($versionId);

        /** @var VersionControlCmsGateway $cmsGateway */
        $cmsGateway = $this->getContainer()->get(VersionControlCmsGateway::class);

        $activity = $cmsGateway
            ->searchActivities(new Criteria(), $versionContext)
            ->first();

        static::assertSame($name, $activity->get('name'));
    }

    public function testDraftActionSavesDefaultNameInActivityLog(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->draft($pageId, null, $context);
        $versionContext = $context->createWithVersionId($versionId);

        /** @var VersionControlCmsGateway $cmsGateway */
        $cmsGateway = $this->getContainer()->get(VersionControlCmsGateway::class);

        $activity = $cmsGateway
            ->searchActivities(new Criteria(), $versionContext)
            ->first();

        static::assertSame('enterprise', $activity->get('name'));
    }

    private function updatePreviewMediaId(string $pageId, string $mediaId, Context $context): void
    {
        $this->getContainer()->get('cms_page.repository')->update([[
            'id' => $pageId,
            'previewMediaId' => $mediaId,
        ]], $context);
    }

    private function fetchAnotherMediaIdAsOriginal(string $originalMediaId, Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new NotFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('id', $originalMediaId),
        ]));

        return $this->getContainer()
            ->get('media.repository')
            ->searchIds($criteria, $context)
            ->firstId();
    }

    private function assertPreviewMedia(
        string $pageId,
        string $originalMediaId,
        Context $versionContext,
        ?string $draftMediaId = null
    ): void {
        if (!$draftMediaId) {
            $draftMediaId = $originalMediaId;
        }

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $versionPage = $cmsPageRepository
            ->search(self::createVersionCriteria($pageId), $versionContext)
            ->first();

        static::assertSame($draftMediaId, $versionPage->getPreviewMediaId());
        static::assertSame($draftMediaId, $versionPage->getExtension(self::DRAFT_EXTENSION)->first()['previewMediaId']);

        $originalPage = $cmsPageRepository
            ->search(new Criteria([$pageId]), $versionContext->createWithVersionId(Defaults::LIVE_VERSION))
            ->first();

        static::assertSame($originalMediaId, $originalPage->getPreviewMediaId());
    }

    private static function createVersionCriteria(string $pageId): Criteria
    {
        $versionCriteria = new Criteria([$pageId]);
        $versionCriteria->addAssociation(self::DRAFT_EXTENSION);

        return $versionCriteria;
    }
}

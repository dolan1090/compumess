<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageEvents;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\PreviewMediaSync;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisherTest\DraftIntegrationTrait;
use SwagPublisherTest\PublisherCmsFixtures;

class PreviewMediaSyncTest extends TestCase
{
    use DraftIntegrationTrait;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testCheckListener(): void
    {
        $subscribedEvents = PreviewMediaSync::getSubscribedEvents();

        static::assertSame([CmsPageEvents::PAGE_WRITTEN_EVENT], \array_keys($subscribedEvents));
        static::assertTrue(\method_exists(PreviewMediaSync::class, $subscribedEvents[CmsPageEvents::PAGE_WRITTEN_EVENT]));
    }

    public function testNoUpdateOfDraftMediaIdBecauseUpdatingOriginalPage(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $versionId = $action->draft($pageId, 'foo', $context);
        $mediaId = $this->fetchFirstMediaId();

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $cmsPageRepository->update([[
            'id' => $pageId,
            'previewMediaId' => $mediaId,
        ]], $context);

        $original = $cmsPageRepository->search(new Criteria([$pageId]), $context)->first();
        static::assertSame($mediaId, $original->getPreviewMediaId());

        $draft = $this->fetchDraftByPageAndVersion($pageId, $versionId);
        static::assertNull($draft->get('previewMediaId'));
    }

    public function testNoUpdateOfDraftMediaIdBecauseNoMediaId(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);
        $versionId = $action->draft($pageId, 'foo', $context);

        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $cmsPageRepository->update([[
            'id' => $pageId,
            'locked' => true,
        ]], $context->createWithVersionId($versionId));

        $draft = $this->fetchDraftByPageAndVersion($pageId, $versionId);
        static::assertNull($draft->get('previewMediaId'));

        $original = $cmsPageRepository->search(new Criteria([$pageId]), $context)->first();
        static::assertNull($original->getPreviewMediaId());
    }

    public function testUpdateMediaIdInVersionUpdatesDraft(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        /** @var VersionControlCmsGateway $draftGateway */
        $draftGateway = $this->getContainer()->get(VersionControlCmsGateway::class);

        $draft = $draftGateway
            ->searchDrafts(CriteriaFactory::forDraftWithPageAndVersion($pageId, $versionId), $context)
            ->first();

        static::assertNull($draft->get('previewMediaId'));

        $mediaId = $this->fetchFirstMediaId();
        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $cmsPageRepository->update([[
            'id' => $pageId,
            'previewMediaId' => $mediaId,
        ]], $versionContext);

        $draft = $draftGateway
            ->searchDrafts(CriteriaFactory::forDraftWithPageAndVersion($pageId, $versionId), $context)
            ->first();

        static::assertSame($mediaId, $draft->get('previewMediaId'));

        $original = $cmsPageRepository->search(new Criteria([$pageId]), $context)->first();
        static::assertNull($original->getPreviewMediaId());
    }

    public function testUpdateMediaIdInMultiplePagesInSingleVersion(): void
    {
        $pageId1 = $this->importPage();
        $context = Context::createDefaultContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->draft($pageId1, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        $pageId2 = $this->importPage($versionContext);
        $pageId3 = $this->importPage($versionContext);

        /** @var EntityRepository $cmsPageRepository */
        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $mediaId = $this->fetchFirstMediaId();

        $cmsPageRepository->update([
            [
                'id' => $pageId1,
                'previewMediaId' => $mediaId,
            ],
            [
                'id' => $pageId2,
                'previewMediaId' => $mediaId,
            ],
            [
                'id' => $pageId3,
                'previewMediaId' => $mediaId,
            ],
        ], $versionContext);

        /** @var VersionControlCmsGateway $draftGateway */
        $draftGateway = $this->getContainer()->get(VersionControlCmsGateway::class);
        $drafts = $draftGateway->searchDrafts(new Criteria(), $versionContext);

        static::assertSame(1, $drafts->count());
        static::assertSame($mediaId, $drafts->first()->get('previewMediaId'));
    }

    public function testUpdateMediaIdWithAdminApiSource(): void
    {
        $pageId = $this->importPage();
        $context = self::createAdminApiContext();

        /** @var DraftAction $action */
        $action = $this->getContainer()->get(DraftAction::class);

        $versionId = $action->draft($pageId, 'foo', $context);
        $versionContext = $context->createWithVersionId($versionId);

        /** @var VersionControlCmsGateway $draftGateway */
        $draftGateway = $this->getContainer()->get(VersionControlCmsGateway::class);

        $draft = $draftGateway
            ->searchDrafts(CriteriaFactory::forDraftWithPageAndVersion($pageId, $versionId), $context)
            ->first();

        static::assertNull($draft->get('previewMediaId'));

        $mediaId = $this->fetchFirstMediaId();
        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        $cmsPageRepository->update([[
            'id' => $pageId,
            'previewMediaId' => $mediaId,
        ]], $versionContext);

        $draft = $draftGateway
            ->searchDrafts(CriteriaFactory::forDraftWithPageAndVersion($pageId, $versionId), $context)
            ->first();

        static::assertSame($mediaId, $draft->get('previewMediaId'));

        $original = $cmsPageRepository->search(new Criteria([$pageId]), $context)->first();
        static::assertNull($original->getPreviewMediaId());
    }

    private static function createAdminApiContext(): Context
    {
        $adminApiSource = new AdminApiSource(null);
        $adminApiSource->setIsAdmin(true);

        return Context::createDefaultContext($adminApiSource);
    }
}

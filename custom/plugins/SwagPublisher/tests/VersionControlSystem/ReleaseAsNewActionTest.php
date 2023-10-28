<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\ReleaseAsNewAction;
use SwagPublisherTest\DraftIntegrationTrait;
use SwagPublisherTest\PublisherCmsFixtures;
use SwagPublisherTest\TestCaseBase\Cms\CmsTestTrait;

class ReleaseAsNewActionTest extends TestCase
{
    use ActionBehaviour;
    use CmsTestTrait;
    use DraftIntegrationTrait;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testReleasesAreLogged(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $versionId = $this->getDraftAction()->draft($pageId, 'foo', $context);

        $action = $this->getContainer()->get(ReleaseAsNewAction::class);
        static::assertInstanceOf(ReleaseAsNewAction::class, $action);

        $action->releaseAsNew($pageId, $versionId, $context);

        $this->assertSingleLogExistsWith('isReleasedAsNew', $context);
    }

    public function testReleaseTakesOverPreviewMediaId(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $versionId = $this->getDraftAction()
            ->draft($pageId, 'foo', $context);

        $mediaId = $this->fetchFirstMediaId();

        $this->getCmsPageRepository()->update([[
            'id' => $pageId,
            'previewMediaId' => $mediaId,
        ]], $context->createWithVersionId($versionId));

        $releaseAsNewAction = $this->getContainer()->get(ReleaseAsNewAction::class);
        static::assertInstanceOf(ReleaseAsNewAction::class, $releaseAsNewAction);

        $newPageId = $releaseAsNewAction->releaseAsNew($pageId, $versionId, $context);

        /** @var CmsPageEntity $cmsPage */
        $cmsPage = $this->getCmsPageRepository()
            ->search(CriteriaFactory::withIds($newPageId), $context)
            ->first();

        static::assertSame($mediaId, $cmsPage->getPreviewMediaId());
    }

    public function testReleaseAfterUpdatingDraft(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        $versionId = $this->getDraftAction()
            ->draft($pageId, 'foo', $context);

        $versionContext = $context->createWithVersionId($versionId);

        $this->createCmsSection(Uuid::randomHex(), 'test', $pageId, $versionContext);

        $releaseAsNewAction = $this->getContainer()->get(ReleaseAsNewAction::class);
        static::assertInstanceOf(ReleaseAsNewAction::class, $releaseAsNewAction);

        $newPageId = $releaseAsNewAction->releaseAsNew($pageId, $versionId, $versionContext);

        /** @var CmsPageEntity $cmsPage */
        $cmsPage = $this->getCmsPageRepository()
            ->search(CriteriaFactory::withIds($newPageId), $context)
            ->first();

        static::assertSame(Defaults::LIVE_VERSION, $cmsPage->getVersionId());
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
            ]], $context);
    }

    private function getDraftAction(): DraftAction
    {
        $draftAction = $this->getContainer()->get(DraftAction::class);

        static::assertInstanceOf(DraftAction::class, $draftAction);

        return $draftAction;
    }
}

<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\MergeAction;
use SwagPublisherTest\DraftIntegrationTrait;
use SwagPublisherTest\PublisherCmsFixtures;

class MergeActionTest extends TestCase
{
    use ActionBehaviour;
    use DraftIntegrationTrait;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testMergesAreLogged(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();
        $versionId = $this->getContainer()->get(DraftAction::class)->draft($pageId, 'foo', $context);

        $action = $this->getContainer()->get(MergeAction::class);

        $action->merge($pageId, $versionId, $context);

        $this->assertSingleLogExistsWith('isMerged', $context);
    }

    public function testMergeWritesPreviewMediaIdIntoPage(): void
    {
        $pageId = $this->importPage();
        $context = Context::createDefaultContext();

        $originalCmsPage = $this->fetchCmsPage($pageId, $context);
        static::assertNull($originalCmsPage->getPreviewMediaId());

        $versionId = $this->getContainer()->get(DraftAction::class)
            ->draft($pageId, 'foo', $context);

        $mediaId = $this->fetchFirstMediaId();
        $this->getContainer()->get('cms_page.repository')->update([[
            'id' => $pageId,
            'previewMediaId' => $mediaId,
        ]], $context->createWithVersionId($versionId));

        $action = $this->getContainer()->get(MergeAction::class);
        $action->merge($pageId, $versionId, $context);

        $originalCmsPage = $this->fetchCmsPage($pageId, $context);
        static::assertSame($mediaId, $originalCmsPage->getPreviewMediaId());
    }

    private function fetchCmsPage(string $id, Context $context): CmsPageEntity
    {
        return $this->getContainer()
            ->get('cms_page.repository')
            ->search(CriteriaFactory::withIds($id), $context)
            ->first();
    }
}

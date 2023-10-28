<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisher\VersionControlSystem\Internal\CriteriaFactory;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use SwagPublisher\VersionControlSystem\Internal\VersionControlService;
use SwagPublisherTest\PublisherCmsFixtures;

class CriteriaFactoryTest extends TestCase
{
    use AdminApiTestBehaviour;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    public function testPageByRelationSelection(): void
    {
        $context = Context::createDefaultContext();
        $repository = $this->getContainer()->get('cms_page.repository');
        $pageRaw = $this->getCmsPageFixture();

        $repository->create($pageRaw, $context);
        $singlePage = $pageRaw[0];

        $pageId = $singlePage['id'];
        $sectionId = $singlePage['sections'][0]['id'];
        $blockId = $singlePage['sections'][0]['blocks'][0]['id'];
        $slotId = $singlePage['sections'][0]['blocks'][0]['slots'][0]['id'];

        static::assertEquals($pageId, $repository->search(CriteriaFactory::forPageBySlotId($slotId), $context)->first()->getId());
        static::assertEquals($pageId, $repository->search(CriteriaFactory::forPageByBlockId($blockId), $context)->first()->getId());
        static::assertEquals($pageId, $repository->search(CriteriaFactory::forPageBySectionId($sectionId), $context)->first()->getId());
    }

    public function testFetchPageWithVersion(): void
    {
        $context = Context::createDefaultContext();
        $pageId = $this->importPage($context);

        $versionContext = $this->getContainer()
            ->get(VersionControlService::class)->branch($pageId, CmsPageDefinition::ENTITY_NAME, $context);

        $pageCollection = $this->getContainer()->get('cms_page.repository')
            ->search(CriteriaFactory::forPageWithVersion($versionContext->getVersionId()), $versionContext);

        static::assertSame(1, $pageCollection->count());

        $versionPage = $pageCollection->first();
        static::assertNotSame($context->getVersionId(), $versionPage->getVersionId());
    }

    public function testFetchActivityByPageAndVersion(): void
    {
        $context = Context::createDefaultContext();
        $pageId = $this->importPage($context);

        $draftName = 'my draft 123';
        $versionId = $this->getContainer()
            ->get(DraftAction::class)->draft($pageId, $draftName, $context);

        $activity = $this->getContainer()->get(VersionControlCmsGateway::class)
            ->searchActivities(CriteriaFactory::forActivityWithPageAndVersion($pageId, $versionId), $context)
            ->first();

        static::assertSame($draftName, $activity->get('name'));
    }
}

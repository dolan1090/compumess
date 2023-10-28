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
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use SwagPublisher\VersionControlSystem\Internal\CleanupConfig;
use SwagPublisher\VersionControlSystem\Internal\CleanupHandler;
use SwagPublisher\VersionControlSystem\Internal\CleanupTask;
use SwagPublisherTest\ContextFactory;
use SwagPublisherTest\RawDatabaseAccess;

class CleanupTest extends TestCase
{
    use ContextFactory;
    use IntegrationTestBehaviour;
    use RawDatabaseAccess;

    private EntityRepository $pageRepository;

    protected function setUp(): void
    {
        $this->pageRepository = $this->getContainer()->get('cms_page.repository');
    }

    public function testCheckTask(): void
    {
        $task = $this->getContainer()->get(CleanupTask::class);
        static::assertInstanceOf(CleanupTask::class, $task);
        static::assertSame(24 * 60 * 60, CleanupTask::getDefaultInterval());
        static::assertSame('swag.publisher_version_control_cleanup', CleanupTask::getTaskName());
    }

    public function testCleanup(): void
    {
        $this->createFixtures();
        $rowCounts = $this->fetchCmsRowCounts();
        static::assertSame('100', $rowCounts['cms_page_activity_count']);
        static::assertSame('100', $rowCounts['cms_page_activity_with_details_count']);

        $config = $this->getContainer()->get(CleanupConfig::class);

        $config->maxLogEntriesPerPage = 3;
        $config->maxLogEntriesWithDetailsPerPage = 1;
        $this->getContainer()->get(CleanupHandler::class)->__invoke(new CleanupTask());

        $rowCounts = $this->fetchCmsRowCounts();
        static::assertSame('15', $rowCounts['cms_page_activity_count']);
        static::assertSame('5', $rowCounts['cms_page_activity_with_details_count']);

        $this->getContainer()->get(CleanupHandler::class)->__invoke(new CleanupTask());

        $rowCounts = $this->fetchCmsRowCounts();
        static::assertSame('15', $rowCounts['cms_page_activity_count']);
        static::assertSame('5', $rowCounts['cms_page_activity_with_details_count']);
    }

    private function createFixtures(): void
    {
        $context = $this->createAdminApiSourceContext();
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('locked', false));
        $ids = $this->pageRepository->searchIds($criteria, $context);

        $config = [];

        foreach ($ids->getIds() as $id) {
            $versionId = Defaults::LIVE_VERSION;
            $config[] = [$id, $versionId, 10];

            $versionId = $this->pageRepository->createVersion($id, $context);
            $config[] = [$id, $versionId, 10];
        }

        $activityRepository = $this->getContainer()->get('cms_page_activity.repository');
        foreach ($config as $entry) {
            [$pageId, $versionId, $logEntries] = $entry;

            for ($i = 0; $i < $logEntries; ++$i) {
                $activityRepository->create([[
                    'pageId' => $pageId,
                    'cmsPageVersionId' => $versionId === Defaults::LIVE_VERSION ? null : $versionId,
                    'details' => [[[[[[[[[['content']]]]]]]]]],
                    'name' => 'foo',
                ]], Context::createDefaultContext());
            }
        }
    }
}

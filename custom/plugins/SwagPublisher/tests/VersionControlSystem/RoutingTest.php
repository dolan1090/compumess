<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseHelper\TestBrowser;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisherTest\PublisherCmsFixtures;
use SwagPublisherTest\RawDatabaseAccess;
use Symfony\Component\HttpFoundation\Response;

class RoutingTest extends TestCase
{
    use AdminApiTestBehaviour;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;
    use RawDatabaseAccess;

    private string $pageId;

    private TestBrowser $testBrowser;

    protected function setUp(): void
    {
        $this->pageId = $this->importPage();
        $this->testBrowser = $this->createClient();
    }

    public function testDraftRoute(): void
    {
        $this->assertArrayValuesEqual('1', $this->fetchCmsVersionCounts());
        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/draft');

        static::assertIsString($response->getContent());
        static::assertTrue($response->isSuccessful(), $response->getContent());
        static::assertTrue(Uuid::isValid(\json_decode($response->getContent(), true)));

        $cmsPageDraftRepository = $this->getContainer()->get('cms_page_draft.repository');
        static::assertInstanceOf(EntityRepository::class, $cmsPageDraftRepository);

        $drafts = $cmsPageDraftRepository->search(new Criteria(), Context::createDefaultContext());
        static::assertCount(1, $drafts);

        $cmsPageActivityRepository = $this->getContainer()->get('cms_page_activity.repository');
        static::assertInstanceOf(EntityRepository::class, $cmsPageActivityRepository);

        $activities = $cmsPageActivityRepository->search(new Criteria(), Context::createDefaultContext());
        static::assertCount(1, $activities);

        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());

        $this->assertRowCount(1, 1, [null]);
    }

    public function testDraftRouteTwice(): void
    {
        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/draft');
        static::assertTrue($response->isSuccessful());

        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/draft');
        static::assertTrue($response->isSuccessful());

        $this->assertArrayValuesEqual('3', $this->fetchCmsVersionCounts());
        $this->assertRowCount(2, 2, [null, null]);
    }

    public function testMergeRoute(): void
    {
        $versionId = $this->getDraftAction()->draft($this->pageId, 'foo', Context::createDefaultContext());
        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());
        $this->assertRowCount(1, 1, [null]);

        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/merge/' . $versionId);

        static::assertTrue($response->isSuccessful(), \print_r($response->getContent(), true));
        $this->assertArrayValuesEqual('1', $this->fetchCmsVersionCounts());
        $this->assertRowCount(0, 1, [null]);
    }

    public function testMergeRouteWithoutDraft(): void
    {
        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/merge/_NOT_THERE_');
        static::assertFalse($response->isSuccessful());
    }

    public function testDiscardRoute(): void
    {
        $versionId = $this->getDraftAction()->draft($this->pageId, 'foo', Context::createDefaultContext());
        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());

        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/discard/' . $versionId);

        static::assertTrue($response->isSuccessful(), \print_r($response->getContent(), true));

        $this->assertRowCount(0, 1, [null]);
        $this->assertArrayValuesEqual('1', $this->fetchCmsVersionCounts());
    }

    public function testReleaseAsNewRoute(): void
    {
        $versionId = $this->getDraftAction()->draft($this->pageId, 'foo', Context::createDefaultContext());
        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());
        static::assertSame('12', $this->fetchCmsRowCounts()['cms_page_count']);

        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/releaseAsNew/' . $versionId);

        static::assertTrue($response->isSuccessful(), \print_r($response->getContent(), true));
        $this->assertArrayValuesEqual('1', $this->fetchCmsVersionCounts());

        $this->assertRowCount(0, 2, [null, 10]);
        static::assertSame('12', $this->fetchCmsRowCounts()['cms_page_count']);
    }

    public function testDuplicateRoute(): void
    {
        $versionId = $this->getDraftAction()->draft($this->pageId, 'foo', Context::createDefaultContext());
        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());
        static::assertSame('12', $this->fetchCmsRowCounts()['cms_page_count']);

        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/duplicate/' . $versionId);

        static::assertTrue($response->isSuccessful(), \print_r($response->getContent(), true));
        $this->assertArrayValuesEqual('3', $this->fetchCmsVersionCounts());

        $this->assertRowCount(2, 2, [null, null]);
        static::assertSame('13', $this->fetchCmsRowCounts()['cms_page_count']);
    }

    public function testUpdateFromLiveVersionRoute(): void
    {
        $versionId = $this->getDraftAction()->draft($this->pageId, 'foo', Context::createDefaultContext());
        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());
        static::assertSame('12', $this->fetchCmsRowCounts()['cms_page_count']);

        $response = $this->request('POST', '_action/cms_page/' . $this->pageId . '/updateFromLiveVersion/' . $versionId);

        static::assertTrue($response->isSuccessful(), \print_r($response->getContent(), true));
        $this->assertArrayValuesEqual('2', $this->fetchCmsVersionCounts());

        $this->assertRowCount(1, 1, [null]);
        static::assertSame('12', $this->fetchCmsRowCounts()['cms_page_count']);
    }

    private function request(string $method, string $apiUri): Response
    {
        $this->testBrowser
            ->request($method, '/api/' . $apiUri);

        return $this->testBrowser
            ->getResponse();
    }

    private function assertArrayValuesEqual(string $value, array $array): void
    {
        foreach ($array as $arrayKey => $arrayValue) {
            static::assertSame($value, $arrayValue, "Error at $arrayKey");
        }
    }

    private function assertRowCount(int $draftCount, int $activityCount, array $detailCounts): void
    {
        $counts = $this->fetchCmsRowCounts();

        static::assertSame((string) $draftCount, $counts['cms_page_draft_count'], 'Draft count error');
        static::assertSame((string) $activityCount, $counts['cms_page_activity_count'], 'Activity count error');

        $activityies = $this->fetchActivityDetails();

        static::assertSame(\array_keys($detailCounts), \array_keys($activityies));
        foreach ($detailCounts as $i => $detailCount) {
            $activity = $activityies[$i];

            if ($detailCount === null) {
                static::assertNull($activity['details'], 'At ' . $i);
            } else {
                static::assertIsString($activity['details'], 'At ' . $i);
                $details = \json_decode($activity['details']);

                static::assertIsArray($details);
                static::assertCount($detailCount, $details, ' At ' . $i . 'Got: ' . \print_r($details, true));
            }
        }
    }

    private function getDraftAction(): DraftAction
    {
        $draftAction = $this->getContainer()->get(DraftAction::class);

        static::assertInstanceOf(DraftAction::class, $draftAction);

        return $draftAction;
    }
}

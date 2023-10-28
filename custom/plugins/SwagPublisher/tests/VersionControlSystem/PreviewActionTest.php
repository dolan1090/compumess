<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\AdminApiTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use SwagPublisher\VersionControlSystem\DraftAction;
use SwagPublisherTest\PublisherCmsFixtures;
use Symfony\Component\HttpFoundation\Response;

class PreviewActionTest extends TestCase
{
    use AdminApiTestBehaviour;
    use IntegrationTestBehaviour;
    use PublisherCmsFixtures;

    private const DRAFT_PREVIEW_ROUTE = 'http://localhost:8000/draft/preview/';

    public function testRenderDraftPreview(): void
    {
        $context = Context::createDefaultContext();
        $versionId = $this->createDraftWithAction($context);

        $deepLink = $this->fetchDeepLinkFromDraft($context->createWithVersionId($versionId));

        $response = $this->requestToPreviewRoute($deepLink);
        static::assertSame(200, $response->getStatusCode(), \print_r($response->getContent(), true));

        /** @var NavigationPage $page */
        $page = $response->getData()['page'];

        $metaInformation = $page->getMetaInformation();
        static::assertSame('Home', $metaInformation->getMetaTitle());
        static::assertSame('en-GB', $metaInformation->getXmlLang());

        $header = $page->getHeader();
        static::assertSame('Euro', $header->getActiveCurrency()->getName());

        $cmsPage = $page->getCmsPage();
        static::assertSame('enterprise', $cmsPage->getName());
        static::assertSame('page', $cmsPage->getType());
        static::assertSame($versionId, $cmsPage->getVersionId());
        static::assertCount(4, $cmsPage->getSections());

        $section = $cmsPage->getSections()->first();
        static::assertCount(4, $section->getBlocks());

        $content = $response->getContent();
        static::assertStringContainsString('<div class="cms-page">', $content);
        static::assertStringContainsString('<div class="cms-sections">', $content);
    }

    public function testRenderHomePageWithErrorMessage(): void
    {
        $response = $this->requestToPreviewRoute('foo');

        $content = $response->getContent();
        static::assertStringContainsString('<div class="alert-content">', $content);
        static::assertStringContainsString('Draft with specified code could not be found', $content);
    }

    public function testRenderWillAddNavigationIdToPage(): void
    {
        if (!\method_exists(NavigationPage::class, 'getNavigationId')) {
            static::markTestSkipped('Method \'getNavigationId\' does not exist. No testing needed.');
        }

        $context = Context::createDefaultContext();
        $versionId = $this->createDraftWithAction($context);

        $deepLink = $this->fetchDeepLinkFromDraft($context->createWithVersionId($versionId));

        $response = $this->requestToPreviewRoute($deepLink);
        static::assertSame(200, $response->getStatusCode(), \print_r($response->getContent(), true));

        /** @var NavigationPage $page */
        $page = $response->getData()['page'];
        static::assertNotNull($page->getNavigationId());
    }

    private function createDraftWithAction(Context $context): string
    {
        return $this->getContainer()
            ->get(DraftAction::class)
            ->draft($this->importPage(), 'foo', $context);
    }

    private function fetchDeepLinkFromDraft(Context $context): string
    {
        return $this->getContainer()->get('cms_page_draft.repository')
            ->search(new Criteria(), $context)
            ->first()
            ->all()['deepLinkCode'];
    }

    private function requestToPreviewRoute(string $deepLinkCode): Response
    {
        $client = $this->createClient();
        $client->request('GET', self::DRAFT_PREVIEW_ROUTE . $deepLinkCode);

        return $client->getResponse();
    }
}

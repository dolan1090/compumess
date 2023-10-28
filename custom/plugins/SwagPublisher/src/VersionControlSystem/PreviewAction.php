<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\SalesChannel\AbstractCategoryRoute;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Shopware\Storefront\Page\Navigation\NavigationPage;
use SwagPublisher\VersionControlSystem\Exception\NotFoundException;
use SwagPublisher\VersionControlSystem\Internal\VersionControlCmsGateway;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class PreviewAction extends StorefrontController
{
    public function __construct(
        private readonly SalesChannelCmsPageLoaderInterface $cmsPageLoader,
        private readonly VersionControlCmsGateway $cmsGateway,
        private readonly AbstractCategoryRoute $cmsPageRoute,
        private readonly GenericPageLoaderInterface $genericLoader
    ) {
    }

    #[Route('/draft/preview/{deepLinkCode}', name: 'frontend.draft.preview', methods: ['GET'])]
    public function onPreview(
        Request $request,
        SalesChannelContext $context
    ): Response {
        $deepLinkCode = $request->attributes
            ->get('deepLinkCode');

        try {
            $draftData = $this->fetchDraftDataByDeepLinkCode($deepLinkCode, $context);
        } catch (NotFoundException $e) {
            $this->addFlash('danger', $this->trans('publisher.preview.draftNotFound'));

            return $this->redirectToRoute('frontend.home.page');
        }

        $page = $this->createNavigationPage($request, $context);
        $context = $this->createSalesChannelContextWithVersionId($draftData['draftVersion'], $context);

        $cmsPages = $this->cmsPageLoader->load(
            $request,
            $this->createCriteria($deepLinkCode),
            $context
        );

        /** @var CmsPageEntity $cmsPage */
        $cmsPage = $cmsPages->first();
        $page->setCmsPage($cmsPage);

        return $this->renderStorefront('@Storefront/storefront/page/content/index.html.twig', [
            'page' => $page,
        ]);
    }

    private function createCriteria(string $deepLinkCode): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('drafts.deepLinkCode', $deepLinkCode));

        return $criteria;
    }

    private function fetchDraftDataByDeepLinkCode(string $deepLinkCode, SalesChannelContext $context): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('deepLinkCode', $deepLinkCode));

        $draft = $this->cmsGateway
            ->searchDrafts($criteria, $context->getContext())
            ->first();

        if (!$draft) {
            throw new NotFoundException('Draft with specified code could not be found');
        }

        return $draft->all();
    }

    private function createSalesChannelContextWithVersionId(
        string $versionId,
        SalesChannelContext $salesChannelContext
    ): SalesChannelContext {
        $context = $salesChannelContext
            ->getContext()
            ->createWithVersionId($versionId);

        return new SalesChannelContext(
            $context,
            $salesChannelContext->getToken(),
            null,
            $salesChannelContext->getSalesChannel(),
            $salesChannelContext->getCurrency(),
            $salesChannelContext->getCurrentCustomerGroup(),
            $salesChannelContext->getTaxRules(),
            $salesChannelContext->getPaymentMethod(),
            $salesChannelContext->getShippingMethod(),
            $salesChannelContext->getShippingLocation(),
            $salesChannelContext->getCustomer(),
            $salesChannelContext->getItemRounding(),
            $salesChannelContext->getTotalRounding(),
            $salesChannelContext->getAreaRuleIds()
        );
    }

    private function loadMetaData(CategoryEntity $category, NavigationPage $page): void
    {
        if (!($metaInformation = $page->getMetaInformation())) {
            return;
        }

        $metaDescription = $category->getTranslation('metaDescription')
            ?? $category->getTranslation('description');
        $metaInformation->setMetaDescription((string) $metaDescription);

        $metaTitle = $category->getTranslation('metaTitle')
            ?? $category->getTranslation('name');
        $metaInformation->setMetaTitle((string) $metaTitle);

        $metaInformation->setMetaKeywords((string) $category->getTranslation('keywords'));
    }

    private function createNavigationPage(Request $request, SalesChannelContext $context): NavigationPage
    {
        $page = $this->genericLoader->load($request, $context);
        /** @var NavigationPage $page */
        $page = NavigationPage::createFrom($page);

        $navigationId = $context->getSalesChannel()
            ->getNavigationCategoryId();

        $category = $this->cmsPageRoute
            ->load($navigationId, $request, $context)
            ->getCategory();

        $this->loadMetaData($category, $page);

        if (\method_exists($page, 'setNavigationId')) {
            $page->setNavigationId($navigationId);
        }

        return $page;
    }
}

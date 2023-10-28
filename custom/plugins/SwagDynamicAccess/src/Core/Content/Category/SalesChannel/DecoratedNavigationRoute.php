<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Core\Content\Category\SalesChannel;

use OpenApi\Annotations as OA;
use Shopware\Core\Content\Category\CategoryCollection;
use Shopware\Core\Content\Category\SalesChannel\AbstractNavigationRoute;
use Shopware\Core\Content\Category\SalesChannel\NavigationRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class DecoratedNavigationRoute extends AbstractNavigationRoute
{
    private AbstractNavigationRoute $inner;

    private SalesChannelRepository $categoryRepository;

    public function __construct(
        AbstractNavigationRoute $inner,
        SalesChannelRepository $categoryRepository
    ) {
        $this->inner = $inner;
        $this->categoryRepository = $categoryRepository;
    }

    public function getDecorated(): AbstractNavigationRoute
    {
        return $this->inner;
    }

    /**
     * @Entity("category")
     * @OA\Post(
     *      path="/navigation/{requestActiveId}/{requestRootId}",
     *      summary="Fetch a navigation menu",
     *      description="This endpoint returns categories that can be used as a page navigation. You can either return them as a tree or as a flat list. You can also control the depth of the tree.

    Instead of passing uuids, you can also use one of the following aliases for the activeId and rootId parameters to get the respective navigations of your sales channel.

     * main-navigation
     * service-navigation
     * footer-navigation",
     *      operationId="readNavigation",
     *      tags={"Store API", "Category"},
     *      @OA\Parameter(name="Api-Basic-Parameters"),
     *      @OA\Parameter(
     *          name="sw-include-seo-urls",
     *          description="Instructs Shopware to try and resolve SEO URLs for the given navigation item",
     *          @OA\Schema(type="boolean"),
     *          in="header",
     *          required=false
     *      ),
     *      @OA\Parameter(
     *          name="requestActiveId",
     *          description="Identifier of the active category in the navigation tree (if not used, just set to the same as rootId).",
     *          @OA\Schema(type="string", pattern="^[0-9a-f]{32}$"),
     *          in="path",
     *          required=true
     *      ),
     *      @OA\Parameter(
     *          name="requestRootId",
     *          description="Identifier of the root category for your desired navigation tree. You can use it to fetch sub-trees of your navigation tree.",
     *          @OA\Schema(type="string", pattern="^[0-9a-f]{32}$"),
     *          in="path",
     *          required=true
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="depth",
     *                  description="Determines the depth of fetched navigation levels.",
     *                  @OA\Schema(type="integer", default="2")
     *              ),
     *              @OA\Property(
     *                  property="buildTree",
     *                  description="Return the categories as a tree or as a flat list.",
     *                  @OA\Schema(type="boolean", default="true")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="All available navigations",
     *          @OA\JsonContent(ref="#/components/schemas/NavigationRouteResponse")
     *     )
     * )
     * @Route("/store-api/navigation/{activeId}/{rootId}", name="store-api.navigation", methods={"GET", "POST"})
     */
    public function load(
        string $activeId,
        string $rootId,
        Request $request,
        SalesChannelContext $context,
        Criteria $criteria
    ): NavigationRouteResponse {
        $response = $this->inner->load($activeId, $rootId, $request, $context, $criteria);

        if (
            $activeId !== $context->getSalesChannel()->getFooterCategoryId()
            && $activeId !== $context->getSalesChannel()->getServiceCategoryId()
        ) {
            return $response;
        }

        $categoryId = $this->categoryRepository->searchIds(new Criteria([$activeId]), $context)->firstId();

        if ($categoryId === null) {
            return new NavigationRouteResponse(new CategoryCollection());
        }

        return $response;
    }
}

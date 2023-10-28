<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Core\Content\Category\SalesChannel;

use OpenApi\Annotations as OA;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Category\SalesChannel\AbstractCategoryRoute;
use Shopware\Core\Content\Category\SalesChannel\CategoryRouteResponse;
use Shopware\Core\Content\Category\Service\CategoryBreadcrumbBuilder;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\CountAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\CountResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class DecoratedCategoryRoute extends AbstractCategoryRoute
{
    private AbstractCategoryRoute $inner;

    private SalesChannelRepository $categoryRepository;

    private CategoryBreadcrumbBuilder $breadcrumbBuilder;

    public function __construct(
        AbstractCategoryRoute $inner,
        SalesChannelRepository $categoryRepository,
        CategoryBreadcrumbBuilder $breadcrumbBuilder
    ) {
        $this->inner = $inner;
        $this->categoryRepository = $categoryRepository;
        $this->breadcrumbBuilder = $breadcrumbBuilder;
    }

    public function getDecorated(): AbstractCategoryRoute
    {
        return $this->inner;
    }

    /**
     * @OA\Post(
     *     path="/category/{categoryId}",
     *     summary="Fetch a single category",
     *     description="This endpoint returns information about the category, as well as a fully resolved (hydrated with mapping values) CMS page, if one is assigned to the category. You can pass slots which should be resolved exclusively.",
     *     operationId="readCategory",
     *     tags={"Store API", "Category"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             description="The product listing criteria only has an effect, if the category contains a product listing.",
     *             ref="#/components/schemas/ProductListingCriteria"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="categoryId",
     *         description="Identifier of the category to be fetched",
     *         @OA\Schema(type="string", pattern="^[0-9a-f]{32}$"),
     *         in="path",
     *         required=true
     *     ),
     *     @OA\Parameter(
     *         name="slots",
     *         description="Resolves only the given slot identifiers. The identifiers have to be seperated by a '|' character",
     *         @OA\Schema(type="string"),
     *         in="query",
     *     ),
     *     @OA\Parameter(name="Api-Basic-Parameters"),
     *     @OA\Response(
     *          response="200",
     *          description="The loaded category with cms page",
     *          @OA\JsonContent(ref="#/components/schemas/Category")
     *     )
     * )
     *
     * @Route("/store-api/category/{navigationId}", name="store-api.category.detail", methods={"GET","POST"})
     */
    public function load(string $navigationId, Request $request, SalesChannelContext $context): CategoryRouteResponse
    {
        $response = $this->inner->load($navigationId, $request, $context);

        $breadcrumb = $this->breadcrumbBuilder->build($response->getCategory(), $context->getSalesChannel());

        if ($breadcrumb === null) {
            return $response;
        }

        unset($breadcrumb[$response->getCategory()->getId()]);
        if (empty($breadcrumb)) {
            return $response;
        }

        $categoryIds = \array_keys($breadcrumb);

        $criteria = new Criteria($categoryIds);
        $criteria->setLimit(1);
        $criteria->addAggregation(new CountAggregation('categories', 'id'));

        /** @var CountResult|null $countAggregation */
        $countAggregation = $this->categoryRepository->aggregate($criteria, $context)->get('categories');

        if ($countAggregation === null) {
            throw new CategoryNotFoundException($response->getCategory()->getId());
        }

        if ($countAggregation->getCount() !== \count($categoryIds)) {
            throw new CategoryNotFoundException($response->getCategory()->getId());
        }

        return $response;
    }
}

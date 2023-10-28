<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\SalesChannel;

use OpenApi\Annotations as OA;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\Entity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}, "_entity"="swag_customized_products_template_option_value"})
 */
class TemplateOptionValueListRoute extends AbstractTemplateOptionValueListRoute
{
    public function __construct(private readonly SalesChannelRepository $templateOptionValueRepository)
    {
    }

    public function getDecorated(): AbstractTemplateOptionValueListRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *      path="/swag_customized_products_template_option_value",
     *      summary="This route can be used to load the custom product template option values by specific filters",
     *      operationId="readCustomProductsTemplateOptionValues",
     *      tags={"Store API", "Custom Products"},
     *
     *      @OA\Parameter(name="Api-Basic-Parameters"),
     *
     *      @OA\Response(
     *          response="200",
     *          description="",
     *
     *          @OA\JsonContent(type="object",
     *
     *              @OA\Property(
     *                  property="total",
     *                  type="integer",
     *                  description="Total amount"
     *              ),
     *              @OA\Property(
     *                  property="aggregations",
     *                  type="object",
     *                  description="aggregation result"
     *              ),
     *              @OA\Property(
     *                  property="elements",
     *                  type="array"
     *              )
     *          )
     *     )
     * )
     *
     * @Route(
     *     "/store-api/swag_customized_products_template_option_value",
     *     name="store-api.swag-customized-products-template-option-value.search",
     *     methods={"GET", "POST"},
     *     defaults={"_entity"="swag_customized_products_template_option_value"}
     * )
     */
    public function load(Criteria $criteria, SalesChannelContext $context): TemplateOptionValueListResponse
    {
        return new TemplateOptionValueListResponse($this->templateOptionValueRepository->search($criteria, $context));
    }
}

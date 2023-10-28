<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\SalesChannel;

use OpenApi\Annotations as OA;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Routing\Annotation\Entity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}, "_entity"="swag_customized_products_template_configuration"})
 */
class TemplateConfigurationListRoute extends AbstractTemplateConfigurationListRoute
{
    public function __construct(private readonly SalesChannelRepository $templateConfigurationRepository)
    {
    }

    public function getDecorated(): AbstractTemplateConfigurationListRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *      path="/swag_customized_products_template_configuration",
     *      summary="This route can be used to load the custom product template configurations by specific filters",
     *      operationId="readCustomProductsTemplateConfigurations",
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
     *     "/store-api/swag_customized_products_template_configuration",
     *     name="store-api.swag-customized-products-template-configuration.search",
     *     methods={"GET", "POST"},
     *     defaults={"_entity"="swag_customized_products_template_configuration"}
     * )
     */
    public function load(Criteria $criteria, SalesChannelContext $context): TemplateConfigurationListResponse
    {
        return new TemplateConfigurationListResponse($this->templateConfigurationRepository->search($criteria, $context));
    }
}

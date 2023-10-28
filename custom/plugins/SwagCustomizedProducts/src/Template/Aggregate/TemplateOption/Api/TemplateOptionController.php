<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Api;

use OpenApi\Annotations as OA;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class TemplateOptionController extends AbstractController
{
    public function __construct(private readonly TemplateOptionServiceInterface $optionService)
    {
    }

    /**
     * @OA\Get(
     *     path="/_action/swag-customized-products-template-option/types",
     *     description="Get all supported option types",
     *     operationId="getSupportedOptionTypes",
     *     tags={"Admin Api", "SwagCustomizedProductsActions"},
     *
     *     @OA\Response(
     *         response="200",
     *         description="All supported option types",
     *     )
     * )
     *
     * @Route("/api/_action/swag-customized-products-template-option/types", name="api.action.swag-customized-products-template-option.types", methods={"GET"}, defaults={"_acl"={"swag_customized_products_template.viewer"}})
     */
    public function getTypes(): JsonResponse
    {
        return new JsonResponse($this->optionService->getSupportedTypes());
    }
}

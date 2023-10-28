<?php declare(strict_types=1);

namespace Shopware\Commercial\PropertyExtractor\Api;

use OpenApi\Annotations as OA;
use Shopware\Commercial\PropertyExtractor\Domain\Service\PropertyExtractorService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\RoutingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Package('business-ops')]
#[Route(defaults: ['_routeScope' => ['administration']])]
final class PropertyExtractorController
{
    /**
     * @internal
     */
    public function __construct(private PropertyExtractorService $propertyExtractorService)
    {
    }

    /**
     * @OA\Post(
     *     path="/api/_admin/property-extractor/extract",
     *     summary="Extracts properties out of a product description.",
     *     description="Extracts properties out of a product description.",
     *     tags={"Admin API"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                  "description",
     *             },
     *
     *             @OA\Property(
     *                 property="description",
     *                 description="Product description",
     *                 type="string",
     *             )
     *         )
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Missing requirements."
     *     )
     * )
     */
    #[Route(
        path: '/api/_admin/property-extractor/extract',
        name: 'api.admin.property-extractor.extract',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'PROPERTY_EXTRACTOR-3361467\')'
    )]
    public function extract(Request $request, Context $context): JsonResponse
    {
        /** @var string $description */
        $description = $request->request->get('description');

        if (empty($description)) {
            throw RoutingException::missingRequestParameter('description');
        }

        $data = $this->propertyExtractorService->getProperties($description, $context);

        return new JsonResponse($data);
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Api;

use OpenApi\Annotations as OA;
use Shopware\Commercial\Licensing\Feature;
use Shopware\Commercial\Licensing\Features;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 *
 * @Route(defaults={"_routeScope"={"administration"}})
 */
#[Package('merchant-services')]
final class AdminController
{
    private Features $features;

    public function __construct(Features $features)
    {
        $this->features = $features;
    }

    /**
     * @OA\Get(
     *     path="/api/_admin/licensing/features/{type}",
     *     summary="Get all the available commercial plugin features",
     *     description="Get all the available commercial plugin features according to the installed license",
     *     operationId="licensing",
     *     tags={"Admin API", "Licensing"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="The available feature data."
     *     )
     * )
     *
     * @Route("/api/_admin/licensing/features/{type}", name="commercial.api.admin.licensing.features", methods={"GET"})
     */
    public function getAvailableFeatures(?string $type = null): JsonResponse
    {
        $features = License::availableFeatures();

        $features = array_map(function (Feature $feature) {
            return [
                'code' => $feature->code,
                'name' => $feature->name,
                'description' => $feature->description,
                'enabled' => $this->features->isNotDisabled($feature->code),
                'type' => $feature->type,
            ];
        }, $features);

        if ($type) {
            $features = array_values(array_filter($features, fn ($feature) => $feature['type'] === $type));
        }

        return new JsonResponse($features);
    }

    /**
     * @OA\Post(
     *     path="/api/_admin/licensing/features/disable/{feature}",
     *     summary="Disable a commercial feature which is provided by the installed license",
     *     description="Disable a commercial feature which is provided by the installed license",
     *     operationId="licensing",
     *     tags={"Admin API", "Licensing"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Feature was disabled"
     *     )
     * )
     *
     * @Route("/api/_admin/licensing/features/disable/{feature}", name="commercial.api.admin.licensing.features.disable", methods={"POST"})
     */
    public function disableFeature(string $feature): JsonResponse
    {
        $this->features->disable([$feature]);

        return new JsonResponse();
    }

    /**
     * @OA\Post(
     *     path="/api/_admin/licensing/features/enable/{feature}",
     *     summary="Enable a commercial feature which is provided by the installed license",
     *     description="Enable a commercial feature which is provided by the installed license",
     *     operationId="licensing",
     *     tags={"Admin API", "Licensing"},
     *
     *     @OA\Response(
     *          response="200",
     *          description="Feature was enabled"
     *     )
     * )
     *
     * @Route("/api/_admin/licensing/features/enable/{feature}", name="commercial.api.admin.licensing.features.enable", methods={"POST"})
     */
    public function enableFeature(string $feature): JsonResponse
    {
        $this->features->enable([$feature]);

        return new JsonResponse();
    }
}

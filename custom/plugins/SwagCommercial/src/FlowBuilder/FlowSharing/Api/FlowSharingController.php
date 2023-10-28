<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Api;

use OpenApi\Annotations as OA;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Package('business-ops')]
#[Route(defaults: ['_routeScope' => ['administration']])]
final class FlowSharingController
{
    /**
     * @internal
     */
    public function __construct(private FlowSharingService $flowSharingService)
    {
    }

    /**
     * @OA\Get(
     *     path="/api/_admin/flow-sharing/download/{flowId}",
     *     summary="Get flow data including reference data",
     *     description="Generate flow data and its reference data to json",
     *     operationId="flowSharing",
     *     tags={"Admin API", "Flow Sharing"},
     *
     *     @OA\Parameter(
     *         name="flowId",
     *         description="Identifier of the flow to be exported.",
     *
     *         @OA\Schema(type="string"),
     *         in="path",
     *         required=true
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The flow data."
     *     )
     * )
     */
    #[Route(
        path: '/api/_admin/flow-sharing/download/{flowId}',
        name: 'api.admin.flow-sharing.download',
        methods: ['GET'],
        condition: 'service(\'license\').check(\'FLOW_BUILDER-4644229\')'
    )]
    public function download(string $flowId, Context $context): JsonResponse
    {
        $data = $this->flowSharingService->download($flowId, $context);

        $response = new JsonResponse($data);
        $response->setEncodingOptions(\JSON_NUMERIC_CHECK);

        return $response;
    }

    /**
     * @OA\Post(
     *     path="/api/_admin/flow-sharing/check-requirements",
     *     summary="Check an environment requirements",
     *     description="Check an environment requirements. For example, shopware version, apps and plugins installed ",
     *     operationId="flowSharing",
     *     tags={"Admin API", "Flow Sharing"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                  "requirements",
     *             },
     *
     *             @OA\Property(
     *                 property="requirements",
     *                 description="Environment requirements",
     *                 type="object",
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
        path: '/api/_admin/flow-sharing/check-requirements',
        name: 'api.admin.flow-sharing.check-requirements',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'FLOW_BUILDER-4644229\')'
    )]
    public function checkRequirements(Request $request, Context $context): JsonResponse
    {
        /** @var array<string, array<int, array<string, string>>|string> $requirements */
        $requirements = $request->request->all('requirements');

        $data = $this->flowSharingService->checkRequirements($requirements, $context);

        return new JsonResponse($data);
    }
}

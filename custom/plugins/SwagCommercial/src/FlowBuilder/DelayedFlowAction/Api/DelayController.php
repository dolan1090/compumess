<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Api;

use OpenApi\Annotations as OA;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\Handler\DelayActionHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Package('business-ops')]
#[Route(defaults: ['_routeScope' => ['api']])]
class DelayController
{
    /**
     * @internal
     */
    public function __construct(private readonly DelayActionHandler $delayHandler)
    {
    }

    /**
     * @Since("6.5.0.0")
     *
     * @OA\Post(
     *      path="/api/_admin/flow-builder/delayed/execute",
     *      summary="Execute the delayed tasks.",
     *      operationId="delayedActions",
     *      description="Execute the delayed tasks.",
     *      tags={"Store API", "Delayed Flow Action"},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={
     *                  "ids"
     *              },
     *
     *              @OA\Property(
     *                  property="ids",
     *                  type="array",
     *                  description="The Identifiers for delayed tasks"),
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="Execute the delayed tasks success."
     *      ),
     *      @OA\Response(
     *          response="401",
     *          description="License exprired"
     *      ),
     *      @OA\Response(
     *          response="500",
     *          description="The Identifiers arr missing or invalid"
     *      )
     * )
     */
    #[Route(
        path: '/api/_admin/flow-builder/delayed/execute',
        name: 'api.admin.flow-builder.delayed.execute',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'FLOW_BUILDER-1811993\')'
    )]
    public function execute(RequestDataBag $data): JsonResponse
    {
        $data = $data->get('ids');
        if (!$data instanceof RequestDataBag) {
            throw new \InvalidArgumentException('Parameter "ids" is missing or must be an array');
        }

        /** @var array<int, string> $ids */
        $ids = $data->all();
        $this->delayHandler->handle($ids);

        return new JsonResponse();
    }
}

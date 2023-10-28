<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Api;

use OpenApi\Annotations as OA;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Routing\RoutingException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('checkout')]
class OrderReturnStateController
{
    /**
     * @internal
     */
    public function __construct(private readonly PositionStateHandler $positionStateHandler)
    {
    }

    /**
     * @Since("6.4.19.0")
     *
     * @OA\Post(
     *     path="/api/_action/order-line-item/state/{transition}",
     *     summary="Transition a list of line-item of an order to a new state",
     *     description="Changes the order's line-items state",
     *     operationId="orderLineItemsTransition",
     *     tags={"Admin API", "Order Return Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                  "ids",
     *             },
     *
     *             @OA\Property(
     *                 property="ids",
     *                 description="A list ID of the order line items, which need to be updated",
     *                 type="array",
     *
     *                 @OA\Items(type="string", pattern="^[0-9a-f]{32}$")
     *             )
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="toState",
     *         description="The `technical_name` of the `state_machine_state`. For example `returned` if the line-item want to change to.",
     *
     *         @OA\Schema(type="string"),
     *         in="path",
     *         required=true
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The updated products return state."
     *     )
     * )
     */
    #[Route(
        path: '/api/_action/order-line-item/state/{toState}',
        name: 'api.action.order_line_item.transition_state',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')'
    )]
    public function transitOrderLineItems(string $toState, Request $request, Context $context): JsonResponse
    {
        $lineItemIds = $request->get('ids');
        if (!\is_array($lineItemIds)) {
            throw RoutingException::missingRequestParameter('ids');
        }

        $updatedLineItemIds = $this->positionStateHandler->transitOrderLineItems($lineItemIds, $toState, $context);

        return new JsonResponse($updatedLineItemIds);
    }

    /**
     * @Since("6.4.19.0")
     *
     * @OA\Post(
     *     path="/api/_action/order-return-line-item/state/{transition}",
     *     summary="Transition a list of the return's line-item to a new state",
     *     description="Changes the state of the return's line-items",
     *     operationId="orderReturnLineItemsTransition",
     *     tags={"Admin API", "Order Return Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                  "ids",
     *             },
     *
     *             @OA\Property(
     *                 property="ids",
     *                 description="A list ID of the order return line items, which need to be updated",
     *                 type="array",
     *
     *                 @OA\Items(type="string", pattern="^[0-9a-f]{32}$")
     *             )
     *         )
     *     ),
     *
     *     @OA\Parameter(
     *         name="toState",
     *         description="The `technical_name` of the `state_machine_state`. For example `returned` if the line-item want to change to.",
     *
     *         @OA\Schema(type="string"),
     *         in="path",
     *         required=true
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The updated products return state."
     *     )
     * )
     */
    #[Route(
        path: '/api/_action/order-return-line-item/state/{toState}',
        name: 'api.action.order_return-line_item.transition_state',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')'
    )]
    public function transitReturnItems(string $toState, Request $request, Context $context): JsonResponse
    {
        $lineItemIds = $request->get('ids');
        if (!\is_array($lineItemIds)) {
            throw RoutingException::missingRequestParameter('ids');
        }

        $updatedLineItemIds = $this->positionStateHandler->transitReturnItems($lineItemIds, $toState, $context);

        return new JsonResponse($updatedLineItemIds);
    }
}

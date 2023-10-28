<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Api;

use Doctrine\DBAL\Connection;
use OpenApi\Annotations as OA;
use Shopware\Commercial\ReturnManagement\Domain\Returning\AbstractOrderReturnRoute;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnException;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnRoute;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Core\Framework\Api\Context\AdminApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('checkout')]
class OrderReturnProxyController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractOrderReturnRoute $orderReturnRoute,
        private readonly SalesChannelContextRestorer $contextRestorer,
        private readonly Connection $connection
    ) {
    }

    /**
     * @Since("6.5.0.0")
     *
     * @OA\Post(
     *     path="/api/_proxy/order/{orderId}/return",
     *     summary="Proxy Store-API to create an return",
     *     description="Create an Return",
     *     operationId="createAReturn",
     *     tags={"Admin API", "Order Return Management"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={
     *                  "createAReturn",
     *             },
     *
     *             @OA\Property(
     *                 property="lineItems",
     *                 description="A list of the order line items payload",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                  @OA\Property(property="orderLineItemId", type="string"),
     *                  @OA\Property(property="quantity", type="number"),
     *                  @OA\Property(property="internalComment", type="string")
     *                 )
     *             )
     *         )
     *      ),
     *
     *      @OA\Parameter(
     *         name="orderId",
     *         description="The `order_id` of these order return line items",
     *
     *         @OA\Schema(type="string"),
     *         in="path",
     *         required=true
     *      ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The return has created successfully."
     *     )
     * )
     */
    #[Route(
        path: '/api/_proxy/order/{orderId}/return',
        name: 'api.proxy.order.return',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')',
    )]
    public function create(string $orderId, Request $request, Context $context): Response
    {
        /* @phpstan-ignore-next-line */
        $salesChannelContext = $this->contextRestorer->restoreByOrder($orderId, $context, [
            SalesChannelContextService::PERMISSIONS => [OrderReturnRoute::ALLOW_CREATE_RETURN_ON_ANY_ORDERS => true],
        ]);

        $returnResp = $this->orderReturnRoute->return($orderId, $request, $salesChannelContext);
        if (!$returnResp) {
            throw OrderReturnException::cannotCreateReturn();
        }

        $userId = $context->getSource() instanceof AdminApiSource ? $context->getSource()->getUserId() : null;
        if (!$userId) {
            return $returnResp;
        }

        /** @var OrderReturnEntity $returnEntity */
        $returnEntity = $returnResp->getObject();

        $returnId = $returnEntity->getId();

        $this->connection->executeStatement(
            'UPDATE `order_return` SET `created_by_id` = :createdById WHERE `id` = :id',
            ['createdById' => Uuid::fromHexToBytes($userId), 'id' => Uuid::fromHexToBytes($returnId)]
        );

        return $returnResp;
    }
}

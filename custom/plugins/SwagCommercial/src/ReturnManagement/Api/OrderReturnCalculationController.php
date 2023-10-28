<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Api;

use OpenApi\Annotations as OA;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnCalculator;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnException;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnStates;
use Shopware\Core\Checkout\Cart\Price\CashRounding;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Bucket\FilterAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Aggregation\Metric\SumAggregation;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\SumResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['api']])]
#[Package('checkout')]
class OrderReturnCalculationController
{
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $orderReturnRepository,
        private readonly CashRounding $rounding,
        private readonly OrderReturnCalculator $returnCalculator
    ) {
    }

    /**
     * @Since("6.5.0.0")
     *
     * @OA\Post(
     *     path="/api/_action/order/return/{orderReturnId}/calculate",
     *     summary="Calculate Amount",
     *     description="Calculate Amount",
     *     operationId="calculateAmount",
     *     tags={"Admin API", "Order Return Management"},
     *
     *      @OA\Parameter(
     *         name="orderReturnId",
     *         description="The `return_id` of order",
     *
     *         @OA\Schema(type="string"),
     *         in="path",
     *         required=true
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The return has calculate amount successfully."
     *     )
     * )
     */
    #[Route(
        path: '/api/_action/order/return/{orderReturnId}/calculate',
        name: 'api.order.return.calculate',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')'
    )]
    public function calculate(string $orderReturnId, Context $context): Response
    {
        $this->returnCalculator->calculate($orderReturnId, $context);

        return new NoContentResponse();
    }

    /**
     * @Since("6.4.19.0")
     *
     * @OA\Post(
     *     path="/api/_action/customer/{customerId}/turnover",
     *     summary="Get turn over value of an customer",
     *     description="Get turn over value of an customer",
     *     operationId="customerTurnOver",
     *     tags={"Admin API", "Order Return Management"},
     *
     *      @OA\Parameter(
     *         name="customerId",
     *         description="The `customer_id` you want to get the turnover",
     *
     *         @OA\Schema(type="string"),
     *         in="path",
     *         required=true
     *     ),
     *
     *      @OA\Response(
     *          response="200",
     *          description="The updated status amount of return line items."
     *     )
     * )
     */
    #[Route(
        path: '/api/_action/customer/{customerId}/turnover',
        name: 'api.customer.turnover',
        methods: ['GET'],
        condition: 'service(\'license\').check(\'RETURNS_MANAGEMENT-8450687\')'
    )]
    public function getTurnOver(string $customerId, Context $context): Response
    {
        $orderCriteria = (new Criteria())->setLimit(1);
        $orderCriteria->addAggregation(
            new FilterAggregation(
                'exceptCancelledOrder',
                new SumAggregation('orderAmount', 'amountTotal'),
                [
                    new NotFilter(
                        MultiFilter::CONNECTION_AND,
                        [
                            new EqualsFilter('stateMachineState.technicalName', OrderStates::STATE_CANCELLED),
                        ]
                    ),
                    new EqualsFilter('orderCustomer.customerId', $customerId),
                ]
            )
        );

        /** @var SumResult|null $aggregationResult */
        $aggregationResult = $this->orderRepository->aggregate($orderCriteria, $context)->get('orderAmount');

        if (!$aggregationResult) {
            throw OrderReturnException::cannotAggregateOrderReturn();
        }

        $orderAmount = $aggregationResult->getSum();
        $returnCriteria = (new Criteria())->setLimit(1);
        $returnCriteria->addAggregation(
            new FilterAggregation(
                'returnedAggregation',
                new SumAggregation('refundedAmount', 'amountTotal'),
                [
                    new EqualsFilter('state.technicalName', OrderReturnStates::STATE_DONE),
                    new EqualsFilter('order.orderCustomer.customerId', $customerId),
                    new EqualsFilter('order.versionId', Defaults::LIVE_VERSION),
                ],
            ),
        );

        /** @var SumResult|null $aggregationResult */
        $aggregationResult = $this->orderReturnRepository->aggregate($returnCriteria, $context)->get('refundedAmount');

        if (!$aggregationResult) {
            throw OrderReturnException::cannotAggregateOrderReturn();
        }

        $returnAmount = $aggregationResult->getSum();
        $turnOver = $orderAmount - $returnAmount;

        $turnOver = $this->rounding->cashRound($turnOver, $context->getRounding());

        return new JsonResponse($turnOver);
    }
}

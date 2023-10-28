<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener\SalesChannel;

use Shopware\Commercial\CheckoutSweetener\Domain\Checkout\SweetenerGenerator;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Order\OrderException;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\NoContentResponse;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @final
 *
 * @internal
 */
#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['store-api']])]
class CheckoutSweetenerRoute
{
    public const ORDER_CUSTOM_FIELD = 'swagCommercialCheckoutSweetener';

    /**
     * @internal
     */
    public function __construct(
        private readonly SweetenerGenerator $checkoutSweetenerGenerator,
        private readonly EntityRepository $orderRepository,
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    #[Route(
        path: '/store-api/checkout/finish/generate-checkout-sweetener',
        name: 'commercial.store-api.generate_checkout-sweetener',
        methods: ['GET'],
        condition: 'service(\'license\').check(\'CHECKOUT_SWEETENER-8945908\')'
    )]
    public function generate(Request $request, SalesChannelContext $context): CheckoutSweetenerResponse|NoContentResponse
    {
        if (!$this->systemConfigService->getBool('core.cart.aiCheckoutMessageActive', $context->getSalesChannelId())) {
            return new NoContentResponse();
        }

        $order = $this->loadOrder($request->request->getAlnum('orderId'), $context);

        $rules = $this->systemConfigService->get('core.cart.aiCheckoutMessageAvailabilityRules', $context->getSalesChannelId());
        if (\is_array($rules) && !empty($rules) && empty(\array_intersect($rules, $order->getRuleIds() ?? []))) {
            return new NoContentResponse();
        }

        $customField = ($order->getCustomFields() ?? [])[self::ORDER_CUSTOM_FIELD] ?? null;
        if (\is_string($customField)) {
            return new CheckoutSweetenerResponse($customField);
        }

        return new CheckoutSweetenerResponse($this->fetchMessage($order, $context));
    }

    public function loadOrder(string $orderId, SalesChannelContext $context): OrderEntity
    {
        if (!$orderId) {
            throw OrderException::orderNotFound($orderId);
        }

        $criteria = new Criteria([$orderId]);
        $criteria->addAssociation('lineItems');
        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($criteria, $context->getContext())->first();

        if ($order === null) {
            throw OrderException::orderNotFound($orderId);
        }

        return $order;
    }

    private function fetchMessage(OrderEntity $order, SalesChannelContext $context): string
    {
        $lineItems = $order->getLineItems();
        if ($lineItems === null || $lineItems->count() === 0) {
            throw OrderException::missingAssociation('lineItems');
        }

        $products = \array_values($lineItems->map(static fn (OrderLineItemEntity $lineItem) => $lineItem->getLabel()));

        $options = [
            'products' => $products,
            'keywords' => $this->systemConfigService->get('core.cart.aiCheckoutMessageKeywords', $context->getSalesChannelId()) ?? '',
        ];

        $length = $this->systemConfigService->getInt('core.cart.aiCheckoutMessageLength', $context->getSalesChannelId());

        if ($length) {
            $options['length'] = $length;
        }

        $message = $this->checkoutSweetenerGenerator->generate($options, $context->getContext())->getText();

        $this->orderRepository->update([[
            'id' => $order->getId(),
            'customFields' => [
                self::ORDER_CUSTOM_FIELD => $message,
            ],
        ]], $context->getContext());

        return $message;
    }
}

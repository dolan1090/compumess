<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Routing\ValueResolver;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Routing\SubscriptionRequest;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

#[Package('checkout')]
class SubscriptionCartValueResolver implements ValueResolverInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        if ($argument->getType() !== Cart::class) {
            return;
        }

        if (!$request->attributes->getBoolean(SubscriptionRequest::ATTRIBUTE_IS_SUBSCRIPTION_CART)) {
            return;
        }

        /** @var SalesChannelContext $context */
        $context = $request->attributes->get(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_SALES_CHANNEL_CONTEXT_OBJECT);

        yield $this->cartService->getCart($context->getToken(), $context);
    }
}

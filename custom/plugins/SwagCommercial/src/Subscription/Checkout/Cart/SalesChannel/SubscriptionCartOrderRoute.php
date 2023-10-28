<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\SalesChannel;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Framework\Event\CheckoutSubscriptionPlacedEvent;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartOrderRoute;
use Shopware\Core\Checkout\Cart\SalesChannel\CartOrderRouteResponse;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class SubscriptionCartOrderRoute extends AbstractCartOrderRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractCartOrderRoute $inner,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function getDecorated(): AbstractCartOrderRoute
    {
        return $this->inner;
    }

    public function order(Cart $cart, SalesChannelContext $context, RequestDataBag $data): CartOrderRouteResponse
    {
        $response = $this->inner->order($cart, $context, $data);

        if (!License::get('SUBSCRIPTIONS-2437281')) {
            return $response;
        }

        $subscriptionContext = $context->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);

        if (!$subscriptionContext) {
            return $response;
        }

        $subscription = $response->getOrder()->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);

        if (!$subscription instanceof SubscriptionEntity) {
            return $response;
        }

        $event = new CheckoutSubscriptionPlacedEvent($context, $subscription);
        $this->eventDispatcher->dispatch($event);

        return $response;
    }
}

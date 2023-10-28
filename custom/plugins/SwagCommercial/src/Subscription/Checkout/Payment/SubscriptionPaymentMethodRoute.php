<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Payment;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Payment\SalesChannel\AbstractPaymentMethodRoute;
use Shopware\Core\Checkout\Payment\SalesChannel\PaymentMethodRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
class SubscriptionPaymentMethodRoute extends AbstractPaymentMethodRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractPaymentMethodRoute $decorated)
    {
    }

    public function getDecorated(): AbstractPaymentMethodRoute
    {
        return $this->decorated;
    }

    public function load(Request $request, SalesChannelContext $context, Criteria $criteria): PaymentMethodRouteResponse
    {
        if (!License::get('SUBSCRIPTIONS-2437281')) {
            return $this->decorated->load($request, $context, $criteria);
        }

        if (!$context->hasExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            return $this->decorated->load($request, $context, $criteria);
        }

        $response = $this->decorated->load($request, $context, $criteria);

        $paymentMethods = $response->getPaymentMethods();

        foreach ($paymentMethods as $paymentMethod) {
            if (!$paymentMethod->isRecurring()) {
                $paymentMethods->remove($paymentMethod->getId());
            }
        }

        return $response;
    }
}

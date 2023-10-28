<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Cart\Event\SubscriptionTransformedEvent;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Commercial\Subscription\Interval\IntervalCalculator;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Checkout\Cart\Order\Transformer\AddressTransformer;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\StateMachine\Loader\InitialStateIdLoader;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class SubscriptionTransformer
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly IntervalCalculator $intervalCalculator,
        private readonly InitialStateIdLoader $initialStateIdLoader,
    ) {
    }

    /**
     * @param array<string, mixed> $convertedCart
     *
     * @return array<string, mixed>
     */
    public function transform(array $convertedCart, SalesChannelContext $salesChannelContext): array
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return $convertedCart;
        }

        if (!$salesChannelContext->hasExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION)) {
            throw SubscriptionCartException::isNotSubscriptionCart();
        }

        /** @var SubscriptionContextStruct $subscriptionExtension */
        $subscriptionExtension = $salesChannelContext->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);

        $billingAddress = $salesChannelContext->getCustomer()?->getActiveBillingAddress();
        $shippingAddress = $salesChannelContext->getCustomer()?->getActiveShippingAddress();

        if (!$billingAddress) {
            throw SubscriptionCartException::missingDataForConversion('activeBillingAddress');
        }

        if (!$shippingAddress) {
            throw SubscriptionCartException::missingDataForConversion('activeShippingAddress');
        }

        $billingAddressTransformed = AddressTransformer::transform($billingAddress);
        $billingAddressTransformed['id'] = $billingAddress->getId();

        $shippingAddressTransformed = AddressTransformer::transform($shippingAddress);
        $shippingAddressTransformed['id'] = $shippingAddress->getId();

        $data = [
            'convertedOrder' => $this->cleanUpCart($convertedCart),
            'salesChannelId' => $this->getValue($convertedCart, 'salesChannelId'),
            'subscriptionPlanId' => $subscriptionExtension->getPlan()->getId(),
            'subscriptionPlanName' => $subscriptionExtension->getPlan()->getTranslation('name'),
            'subscriptionIntervalId' => $subscriptionExtension->getInterval()->getId(),
            'subscriptionIntervalName' => $subscriptionExtension->getInterval()->getTranslation('name'),
            'dateInterval' => $subscriptionExtension->getInterval()->getDateInterval(),
            'cronInterval' => $subscriptionExtension->getInterval()->getCronInterval(),
            'billingAddress' => $billingAddressTransformed,
            'shippingAddress' => $shippingAddressTransformed,
            'paymentMethodId' => $salesChannelContext->getPaymentMethod()->getId(),
            'shippingMethodId' => $salesChannelContext->getShippingMethod()->getId(),
            'stateId' => $this->initialStateIdLoader->get(SubscriptionStates::STATE_MACHINE),
            'currencyId' => $this->getValue($convertedCart, 'currencyId'),
            'itemRounding' => $this->getValue($convertedCart, 'itemRounding'),
            'totalRounding' => $this->getValue($convertedCart, 'totalRounding'),
            'initialExecutionCount' => $subscriptionExtension->getPlan()->getMinimumExecutionCount() ?? 0,
            'remainingExecutionCount' => $subscriptionExtension->getPlan()->getMinimumExecutionCount() ?? 0,
        ];

        $orderDateTime = $this->getValue($convertedCart, 'orderDateTime');

        if (!\is_string($orderDateTime)) {
            throw SubscriptionCartException::missingDataForConversion('orderDateTime');
        }

        $orderDateTime = new \DateTime($orderDateTime);

        $data['nextSchedule'] = $this->intervalCalculator->getNextRunDate($subscriptionExtension->getInterval(), $orderDateTime);
        $data['subscriptionNumber'] = $this->numberRangeValueGenerator->getValue(
            SubscriptionDefinition::ENTITY_NAME,
            $salesChannelContext->getContext(),
            $salesChannelContext->getSalesChannel()->getId()
        );

        $this->eventDispatcher->dispatch(new SubscriptionTransformedEvent($data, $salesChannelContext));

        return $data;
    }

    /**
     * @param array<string, mixed> $convertedCart
     *
     * @return array<string, mixed>
     */
    private function cleanUpCart(array $convertedCart): array
    {
        unset($convertedCart['id']);
        unset($convertedCart['orderNumber']);
        unset($convertedCart['deepLinkCode']);

        return $convertedCart;
    }

    /**
     * @param array<string, mixed> $convertedCart
     */
    private function getValue(array $convertedCart, string $key): mixed
    {
        if (!\array_key_exists($key, $convertedCart)) {
            throw SubscriptionCartException::missingDataForConversion($key);
        }

        return $convertedCart[$key];
    }
}

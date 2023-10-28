<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation;

use Shopware\Commercial\Subscription\Checkout\Order\Generation\Event\BeforeGenerateSubscriptionOrderEvent;
use Shopware\Commercial\Subscription\Entity\Subscription\Aggregate\SubscriptionStateHandler;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Interval\IntervalCalculator;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\Order\OrderConverter;
use Shopware\Core\Checkout\Cart\Order\Transformer\AddressTransformer;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\PaymentRecurringProcessor;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Package('checkout')]
class GenerateSubscriptionOrderService
{
    /**
     * @internal
     *
     * @param EntityRepository<OrderCollection> $orderRepository
     * @param EntityRepository<SubscriptionCollection> $subscriptionRepository
     */
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly EntityRepository $subscriptionRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly IntervalCalculator $intervalCalculator,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly OrderConverter $orderConverter,
        private readonly PaymentRecurringProcessor $paymentRecurringProcessor,
        private readonly SubscriptionStateHandler $subscriptionStateHandler,
    ) {
    }

    public function generateOrderFromSubscription(string $subscriptionId, Context $context, bool $ignoreSchedule = false): void
    {
        $criteria = new Criteria([$subscriptionId]);
        $criteria->addAssociation('subscriptionInterval');
        $criteria->addAssociation('billingAddress.country');
        $criteria->addAssociation('shippingAddress.country');

        /** @var SubscriptionEntity|null $subscription */
        $subscription = $this->subscriptionRepository->search($criteria, $context)->first();

        if (!$subscription) {
            throw SubscriptionTaskException::subscriptionNotFound($subscriptionId);
        }

        if (!$ignoreSchedule && $subscription->getNextSchedule() >= new \DateTime()) {
            return;
        }

        if ($subscription->getStateMachineState()?->getTechnicalName() === SubscriptionStates::STATE_PAUSED) {
            $this->handlePausedSubscription($subscription, $context);

            return;
        }

        $order = $subscription->getConvertedOrder();

        if (!\array_key_exists('salesChannelId', $order) || !\is_string($order['salesChannelId'])) {
            throw SubscriptionTaskException::invalidArgument('salesChannelId');
        }

        $orderId = Uuid::randomHex();

        $order['id'] = $orderId;
        $order['orderNumber'] = $this->numberRangeValueGenerator->getValue(
            OrderDefinition::ENTITY_NAME,
            $context,
            $order['salesChannelId']
        );

        $order['deepLinkCode'] = Random::getBase64UrlString(32);
        $order['orderDateTime'] = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
        $order['subscriptionId'] = $subscription->getId();

        if (!$subscription->getBillingAddress()) {
            throw SubscriptionTaskException::invalidArgument('billingAddress');
        }

        if (!$subscription->getShippingAddress()) {
            throw SubscriptionTaskException::invalidArgument('shippingAddress');
        }

        $billingAddress = AddressTransformer::transform($subscription->getBillingAddress());
        $billingAddress['id'] = $subscription->getBillingAddressId();
        $order['billingAddress'] = $billingAddress;

        $shippingAddress = AddressTransformer::transform($subscription->getShippingAddress());
        $shippingAddress['id'] = $subscription->getShippingAddressId();

        if (!\is_iterable($order['deliveries'])) {
            throw SubscriptionTaskException::invalidArgument('deliveries');
        }

        foreach ($order['deliveries'] as &$delivery) {
            if (!\array_key_exists('shippingOrderAddress', $delivery)) {
                throw SubscriptionTaskException::invalidArgument('shippingOrderAddress');
            }

            $delivery['shippingOrderAddress'] = $shippingAddress;
        }

        /** @var SubscriptionIntervalEntity $subscriptionInterval */
        $subscriptionInterval = $subscription->getSubscriptionInterval();

        $update = [
            'id' => $subscription->getId(),
            'nextSchedule' => $this->intervalCalculator->getNextRunDate(
                $subscriptionInterval,
                $subscription->getNextSchedule()
            ),
        ];

        $event = new BeforeGenerateSubscriptionOrderEvent($order, $update, $context);
        $this->eventDispatcher->dispatch($event);

        $orderWriteResult = $this->orderRepository->upsert([$order], $context);
        $this->subscriptionRepository->update([$update], $context);

        $this->dispatchCheckoutOrderEvents($orderWriteResult);

        try {
            $this->paymentRecurringProcessor->processRecurring($orderId, $context);
        } catch (\Throwable $e) {
            $this->subscriptionStateHandler->failPayment($subscription->getId(), $context);

            throw $e;
        }
    }

    private function handlePausedSubscription(SubscriptionEntity $subscription, Context $context): void
    {
        if (!$subscription->getSubscriptionInterval()) {
            return;
        }

        $update = [
            'id' => $subscription->getId(),
            'nextSchedule' => $this->intervalCalculator->getNextRunDate($subscription->getSubscriptionInterval()),
        ];

        $this->subscriptionRepository->update([$update], $context);
        $this->subscriptionStateHandler->activate($subscription->getId(), $context);
    }

    private function dispatchCheckoutOrderEvents(EntityWrittenContainerEvent $orderWriteResult): void
    {
        $ids = $orderWriteResult->getPrimaryKeys(OrderDefinition::ENTITY_NAME);

        if (!$ids) {
            return;
        }

        foreach ($ids as $orderId) {
            $context = $this->assembleSalesChannelContext($orderId, $orderWriteResult->getContext());

            $criteria = new Criteria([$orderId]);
            $criteria
                ->addAssociation('orderCustomer.customer')
                ->addAssociation('orderCustomer.salutation')
                ->addAssociation('deliveries.shippingMethod')
                ->addAssociation('deliveries.shippingOrderAddress.country')
                ->addAssociation('deliveries.shippingOrderAddress.countryState')
                ->addAssociation('transactions.paymentMethod')
                ->addAssociation('lineItems.cover')
                ->addAssociation('lineItems.downloads.media')
                ->addAssociation('currency')
                ->addAssociation('addresses.country')
                ->addAssociation('addresses.countryState')
                ->addAssociation('stateMachineState')
                ->addAssociation('deliveries.stateMachineState')
                ->addAssociation('transactions.stateMachineState')
                ->addAssociation('subscription.subscriptionInterval')
                ->addAssociation('subscription.subscriptionPlan')
                ->addAssociation('subscription.subscriptionCustomer')
                ->getAssociation('transactions')->addSorting(new FieldSorting('createdAt'));

            $event = new CheckoutOrderPlacedCriteriaEvent($criteria, $context);
            $this->eventDispatcher->dispatch($event);

            /** @var OrderEntity|null $order */
            $order = $this->orderRepository->search($criteria, $context->getContext())->first();

            if (!$order) {
                throw CartException::invalidPaymentOrderNotStored($orderId);
            }

            $event = new CheckoutOrderPlacedEvent($context->getContext(), $order, $order->getSalesChannelId());
            $this->eventDispatcher->dispatch($event);
        }
    }

    private function assembleSalesChannelContext(string $orderId, Context $baseContext): SalesChannelContext
    {
        $criteria = new Criteria([$orderId]);
        $criteria
            ->addAssociation('orderCustomer')
            ->addAssociation('transactions')
            ->addAssociation('deliveries');

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($criteria, $baseContext)->first();

        if (!$order) {
            throw CartException::invalidPaymentOrderNotStored($orderId);
        }

        return $this->orderConverter->assembleSalesChannelContext($order, $baseContext);
    }
}

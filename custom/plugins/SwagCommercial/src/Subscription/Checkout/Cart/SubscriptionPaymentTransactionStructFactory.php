<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart;

use Shopware\Commercial\Subscription\Checkout\Cart\Recurring\SubscriptionRecurringDataStruct;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Commercial\Subscription\Extension\OrderSubscriptionExtension;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\AbstractPaymentTransactionStructFactory;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PreparedPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\RecurringPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\SyncPaymentTransactionStruct;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionPaymentTransactionStructFactory extends AbstractPaymentTransactionStructFactory
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractPaymentTransactionStructFactory $decorated,
    ) {
    }

    public function getDecorated(): AbstractPaymentTransactionStructFactory
    {
        return $this->decorated;
    }

    public function sync(OrderTransactionEntity $orderTransaction, OrderEntity $order): SyncPaymentTransactionStruct
    {
        return new SyncPaymentTransactionStruct(
            $orderTransaction,
            $order,
            $this->createRecurringDataStruct($order)
        );
    }

    public function async(OrderTransactionEntity $orderTransaction, OrderEntity $order, string $returnUrl): AsyncPaymentTransactionStruct
    {
        return new AsyncPaymentTransactionStruct(
            $orderTransaction,
            $order,
            $returnUrl,
            $this->createRecurringDataStruct($order)
        );
    }

    public function prepared(OrderTransactionEntity $orderTransaction, OrderEntity $order): PreparedPaymentTransactionStruct
    {
        return new PreparedPaymentTransactionStruct(
            $orderTransaction,
            $order,
            $this->createRecurringDataStruct($order)
        );
    }

    public function recurring(OrderTransactionEntity $orderTransaction, OrderEntity $order): RecurringPaymentTransactionStruct
    {
        return new RecurringPaymentTransactionStruct(
            $orderTransaction,
            $order,
            $this->createRecurringDataStruct($order)
        );
    }

    private function createRecurringDataStruct(OrderEntity $order): ?SubscriptionRecurringDataStruct
    {
        if (!$order->hasExtension(OrderSubscriptionExtension::SUBSCRIPTION_EXTENSION)) {
            return null;
        }

        $subscription = $order->getExtension(OrderSubscriptionExtension::SUBSCRIPTION_EXTENSION);

        if (!$subscription instanceof SubscriptionEntity) {
            return null;
        }

        return new SubscriptionRecurringDataStruct($subscription);
    }
}

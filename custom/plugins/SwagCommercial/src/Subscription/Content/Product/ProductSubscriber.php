<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Content\Product;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Cart\Discount\SubscriptionDiscountCalculator;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SalesChannel\SalesChannelSubscriptionPlanCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SalesChannel\SalesChannelSubscriptionPlanEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class ProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SubscriptionDiscountCalculator $discountCalculator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.' . ProductEvents::PRODUCT_LOADED_EVENT => ['onProductsLoaded', -100],
            'sales_channel.product.process.criteria' => ['onProcessCriteria', 100],
        ];
    }

    public function onProductsLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        /** @var SalesChannelProductEntity $product */
        foreach ($event->getEntities() as $product) {
            /** @var SalesChannelSubscriptionPlanCollection|null $plans */
            $plans = $product->getExtension('subscriptionPlans');

            if (!$plans) {
                continue;
            }

            $plans = $plans->filterAvailablePlans($event->getContext());

            foreach ($plans->getElements() as $plan) {
                /** @var SalesChannelSubscriptionPlanEntity $plan */
                $discountPrice = $this->discountCalculator->calculateProduct($plan, $product, $event->getSalesChannelContext());
                $plan->setDiscountPrice($discountPrice);
            }

            $product->addExtension('subscriptionPlans', $plans);
        }
    }

    public function onProcessCriteria(SalesChannelProcessCriteriaEvent $event): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $criteria = $event->getCriteria();
        $criteria->addAssociation('subscriptionPlans.subscriptionIntervals');
    }
}

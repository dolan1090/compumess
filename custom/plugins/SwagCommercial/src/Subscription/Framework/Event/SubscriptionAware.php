<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Event;

use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
interface SubscriptionAware extends FlowEventAware
{
    public const SUBSCRIPTION = 'subscription';

    public const SUBSCRIPTION_ID = 'subscriptionId';

    public function getSubscriptionId(): string;
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractSubscriptionPauseRoute
{
    abstract public function getDecorated(): AbstractSubscriptionPauseRoute;

    abstract public function pause(Request $request, SalesChannelContext $context, string $subscriptionId): SubscriptionStateResponse;
}

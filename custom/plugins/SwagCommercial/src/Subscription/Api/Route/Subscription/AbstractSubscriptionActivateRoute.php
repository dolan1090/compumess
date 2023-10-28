<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractSubscriptionActivateRoute
{
    abstract public function getDecorated(): AbstractSubscriptionActivateRoute;

    abstract public function activate(Request $request, SalesChannelContext $context, string $subscriptionId): SubscriptionStateResponse;
}

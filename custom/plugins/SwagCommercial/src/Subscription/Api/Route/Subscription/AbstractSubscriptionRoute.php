<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('checkout')]
abstract class AbstractSubscriptionRoute
{
    abstract public function getDecorated(): AbstractSubscriptionRoute;

    abstract public function load(Request $request, SalesChannelContext $context, Criteria $criteria): SubscriptionRouteResponse;
}

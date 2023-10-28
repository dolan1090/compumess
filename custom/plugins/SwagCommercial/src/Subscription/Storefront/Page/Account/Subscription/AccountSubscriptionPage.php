<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront\Page\Account\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Storefront\Framework\Page\StorefrontSearchResult;
use Shopware\Storefront\Page\Page;

#[Package('checkout')]
class AccountSubscriptionPage extends Page
{
    /**
     * @var StorefrontSearchResult<SubscriptionCollection>
     */
    protected StorefrontSearchResult $subscriptions;

    /**
     * @return StorefrontSearchResult<SubscriptionCollection>
     */
    public function getSubscriptions(): StorefrontSearchResult
    {
        return $this->subscriptions;
    }

    /**
     * @param StorefrontSearchResult<SubscriptionCollection> $subscriptions
     */
    public function setSubscriptions(StorefrontSearchResult $subscriptions): void
    {
        $this->subscriptions = $subscriptions;
    }
}

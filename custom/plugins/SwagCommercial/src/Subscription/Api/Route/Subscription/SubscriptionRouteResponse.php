<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('checkout')]
class SubscriptionRouteResponse extends StoreApiResponse
{
    /**
     * @var EntitySearchResult<SubscriptionCollection>
     */
    protected $object;

    /**
     * @param EntitySearchResult<SubscriptionCollection> $object
     */
    public function __construct(EntitySearchResult $object)
    {
        parent::__construct($object);
    }

    /**
     * @return EntitySearchResult<SubscriptionCollection>
     */
    public function getSubscriptions(): EntitySearchResult
    {
        return $this->object;
    }
}

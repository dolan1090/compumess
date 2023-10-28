<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Api\Route\Subscription;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateEntity;

#[Package('checkout')]
class SubscriptionStateResponse extends StoreApiResponse
{
    /**
     * @var StateMachineStateEntity
     */
    protected $object;

    public function __construct(StateMachineStateEntity $object)
    {
        parent::__construct($object);
    }

    public function getState(): StateMachineStateEntity
    {
        return $this->object;
    }
}

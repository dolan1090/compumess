<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Components\CustomerPrice\Event;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class CustomerPriceGatewayFilterParameterEvent extends Event
{
    private array $customerIds;
    private SalesChannelContext $salesChannelContext;

    public function __construct(array $customerIds, SalesChannelContext $salesChannelContext)
    {
        $this->customerIds = $customerIds;
        $this->salesChannelContext = $salesChannelContext;
    }

    /**
     * @return array
     */
    public function getCustomerIds(): array
    {
        return $this->customerIds;
    }

    /**
     * @param array $customerIds
     */
    public function setCustomerIds(array $customerIds): void
    {
        $this->customerIds = $customerIds;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
    }
}

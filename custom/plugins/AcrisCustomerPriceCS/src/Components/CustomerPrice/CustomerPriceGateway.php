<?php declare(strict_types=1);

namespace Acris\CustomerPrice\Components\CustomerPrice;

use Acris\CustomerPrice\Components\CustomerPrice\Event\CustomerPriceGatewayFilterParameterEvent;
use Acris\CustomerPrice\Components\Filter\CustomerPriceActiveDataRangeFilter;
use Acris\CustomerPrice\Custom\CustomerPriceCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class CustomerPriceGateway
{
    public function __construct(private readonly EntityRepository $customerPriceRepository, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function loadCustomerPricesWithProductIds(SalesChannelContext $salesChannelContext, array $ids, string $customerId): CustomerPriceCollection
    {
        $criteria = $this->addCriteria($salesChannelContext, $ids, $customerId);
        return $this->customerPriceRepository->search($criteria, $salesChannelContext->getContext())->getEntities();
    }

    private function addCriteria(SalesChannelContext $salesChannelContext, array $ids, string $customerId): Criteria
    {
        $activeDateRange = new CustomerPriceActiveDataRangeFilter();

        $customerIds = [$customerId];

        $event = new CustomerPriceGatewayFilterParameterEvent($customerIds, $salesChannelContext);
        $this->eventDispatcher->dispatch($event);

        return (new Criteria())
            ->addFilter(
                new MultiFilter(MultiFilter::CONNECTION_AND, [
                    new EqualsFilter('active', true),
                    new EqualsAnyFilter('customerId', $event->getCustomerIds()),
                    new EqualsAnyFilter('productId', $ids),
                    new MultiFilter(MultiFilter::CONNECTION_OR, [
                        new EqualsAnyFilter('rules.id', $salesChannelContext->getRuleIds()),
                        new EqualsFilter('rules.id', null),
                    ]),
                    $activeDateRange
                    ]
                )
            )
            ->addAssociation('acrisPrices');
    }
}

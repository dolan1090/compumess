<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Store;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('inventory')]
class ProductCloseoutFilterFactoryDecorator extends AbstractProductCloseoutFilterFactory
{
    public function __construct(
        private readonly AbstractProductCloseoutFilterFactory $decorated
    ) {
    }

    public function getDecorated(): AbstractProductCloseoutFilterFactory
    {
        return $this->decorated;
    }

    public function create(SalesChannelContext $context): MultiFilter
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            return $this->decorated->create($context);
        }

        return new NotFilter(NotFilter::CONNECTION_AND, [
            new EqualsFilter('product.isCloseout', true),
            new EqualsFilter('product.available', false),
            new EqualsFilter('product.warehouseGroups.id', null),
        ]);
    }
}

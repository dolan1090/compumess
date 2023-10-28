<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Log\Package;

/**
 * This factory adds more state extensions for OrderLineItem by condition states array, including the null state.
 */
#[Package('checkout')]
final class OrderLineItemStatesCriteriaFactory
{
    /**
     * @internal
     */
    public function __construct()
    {
    }

    /**
     * @param string[] $ids
     * @param string[] $states
     */
    public static function createNotInStates(array $ids, array $states): Criteria
    {
        $criteria = new Criteria($ids);
        $criteria->addAssociation('state');
        $criteria->addAssociation('returns.state');
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('state.technicalName', null),
            new NotFilter(
                MultiFilter::CONNECTION_AND,
                [
                    new EqualsAnyFilter('state.technicalName', $states),
                ]
            ),
        ]));

        return $criteria;
    }
}

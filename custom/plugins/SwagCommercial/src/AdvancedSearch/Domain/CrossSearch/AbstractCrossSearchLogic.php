<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\CrossSearch;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;

/**
 * @experimental
 *
 * @description the cross search is implemented to achieve this scenario:
 * We want to search for manufacturers whose `products.name` is `foo`:
 * 1. We find in `product` index all the products named `foo`, get all these products' manufacturerId using an terms aggregation search.
 * 2. Build a terms query id match these matched ids from step 1 and use this query to search in `manufacturer` index
 */
#[Package('buyers-experience')]
abstract class AbstractCrossSearchLogic
{
    abstract public function getDecorated(): AbstractCrossSearchLogic;

    abstract public function build(EntityDefinition $searchDefinition, Criteria $criteria, Context $context): BoolQuery;
}

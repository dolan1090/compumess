<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Search;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;

/**
 * @experimental
 */
#[Package('buyers-experience')]
abstract class AbstractSearchLogic
{
    abstract public function getDecorated(): AbstractSearchLogic;

    abstract public function build(EntityDefinition $definition, Criteria $criteria, Context $context): BoolQuery;
}

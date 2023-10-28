<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting;

use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Query\Compound\BoolQuery;
use OpenSearchDSL\Query\Compound\ConstantScoreQuery;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolver\EntityStreamResolverRegistry;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\SearchRequestException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Parser\QueryStringParser;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\DataAbstractionLayer\CriteriaParser;

/**
 * Handles a given boosting struct and returns the corresponding ES query object for the `boostingRule`
 */
#[Package('buyers-experience')]
class BoostingQueryBuilder
{
    /**
     * @internal
     */
    public function __construct(
        private readonly BoostingLoader $boostingLoader,
        private readonly EntityStreamResolverRegistry $resolverRegistry,
        private readonly CriteriaParser $criteriaParser
    ) {
    }

    public function build(EntityDefinition $definition, Context $context): ?BuilderInterface
    {
        $contextSource = $context->getSource();

        if (!$contextSource instanceof SalesChannelApiSource) {
            return null;
        }

        $resolver = $this->resolverRegistry->getResolver($definition->getEntityName());
        $boostings = $this->boostingLoader->load($contextSource->getSalesChannelId(), $resolver->getType());
        $resolvedBoostings = $resolver->resolve($boostings);

        $queries = [];
        foreach ($resolvedBoostings as $boosting) {
            if (empty($boosting->getFilter())) {
                continue;
            }

            $filter = QueryStringParser::fromArray($definition, $boosting->getFilter(), new SearchRequestException());

            $query = new ConstantScoreQuery($this->criteriaParser->parseFilter($filter, $definition, $definition->getEntityName(), $context));

            $query->addParameter('boost', $boosting->getBoost());

            $queries[] = $query;
        }

        if (empty($queries)) {
            return null;
        }

        if (\count($queries) === 1) {
            return $queries[0];
        }

        $outputQuery = new BoolQuery();
        foreach ($queries as $query) {
            $outputQuery->add($query, BoolQuery::SHOULD);
        }

        return $outputQuery;
    }
}

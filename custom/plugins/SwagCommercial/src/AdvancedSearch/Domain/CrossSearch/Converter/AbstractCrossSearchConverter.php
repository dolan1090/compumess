<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\CrossSearch\Converter;

use OpenSearchDSL\Aggregation\Bucketing\TermsAggregation;
use OpenSearchDSL\BuilderInterface;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
abstract class AbstractCrossSearchConverter
{
    abstract public function getName(): string;

    abstract public function getAggregation(): TermsAggregation;

    /**
     * @param string[] $result
     */
    abstract public function convertToQuery(array $result): BuilderInterface;
}

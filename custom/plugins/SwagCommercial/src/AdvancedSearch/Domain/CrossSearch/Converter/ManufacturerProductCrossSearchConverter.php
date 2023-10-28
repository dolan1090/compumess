<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\CrossSearch\Converter;

use OpenSearchDSL\Aggregation\Bucketing\TermsAggregation;
use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Query\TermLevel\TermsQuery;
use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('buyers-experience')]
class ManufacturerProductCrossSearchConverter extends AbstractCrossSearchConverter
{
    public function getName(): string
    {
        return sprintf('%s.%s.aggregation', ProductManufacturerDefinition::ENTITY_NAME, ProductDefinition::ENTITY_NAME);
    }

    public function getAggregation(): TermsAggregation
    {
        return new TermsAggregation($this->getName(), 'manufacturerId');
    }

    /**
     * {@inheritdoc}
     */
    public function convertToQuery(array $result): BuilderInterface
    {
        return new TermsQuery('id', $result);
    }
}

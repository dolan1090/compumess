<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Elasticsearch;

use OpenSearchDSL\Search;
use Shopware\Commercial\AdvancedSearch\Domain\Configuration\ConfigurationLoader;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\ElasticsearchHelper;

#[Package('buyers-experience')]
class ElasticsearchHelperDecorator extends ElasticsearchHelper
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ElasticsearchHelper $decorated,
        private readonly ConfigurationLoader $configurationLoader
    ) {
    }

    public function logAndThrowException(\Throwable $exception): bool
    {
        return $this->decorated->logAndThrowException($exception);
    }

    /**
     * Validates if it is allowed do execute the search request over elasticsearch
     */
    public function allowSearch(EntityDefinition $definition, Context $context, Criteria $criteria): bool
    {
        $allowSearch = $this->decorated->allowSearch($definition, $context, $criteria);

        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $allowSearch;
        }

        if (!$allowSearch) {
            return false;
        }

        $source = $context->getSource();
        if (!$source instanceof SalesChannelApiSource) {
            return true;
        }

        $salesChannelId = $source->getSalesChannelId();

        return $this->isEsEnabled($salesChannelId);
    }

    public function addTerm(Criteria $criteria, Search $search, Context $context, EntityDefinition $definition): void
    {
        $this->decorated->addTerm($criteria, $search, $context, $definition);
    }

    public function getIndexName(EntityDefinition $definition/* , ?string $languageId = null */): string
    {
        $languageId = \func_get_args()[1] ?? '';

        if ($languageId === '') {
            return $this->decorated->getIndexName($definition);
        }

        return $this->decorated->getIndexName($definition, $languageId);
    }

    public function allowIndexing(): bool
    {
        return $this->decorated->allowIndexing();
    }

    public function handleIds(EntityDefinition $definition, Criteria $criteria, Search $search, Context $context): void
    {
        $this->decorated->handleIds($definition, $criteria, $search, $context);
    }

    public function addFilters(EntityDefinition $definition, Criteria $criteria, Search $search, Context $context): void
    {
        $this->decorated->addFilters($definition, $criteria, $search, $context);
    }

    public function addPostFilters(EntityDefinition $definition, Criteria $criteria, Search $search, Context $context): void
    {
        $this->decorated->addPostFilters($definition, $criteria, $search, $context);
    }

    public function addQueries(EntityDefinition $definition, Criteria $criteria, Search $search, Context $context): void
    {
        $this->decorated->addQueries($definition, $criteria, $search, $context);
    }

    public function addSortings(EntityDefinition $definition, Criteria $criteria, Search $search, Context $context): void
    {
        $this->decorated->addSortings($definition, $criteria, $search, $context);
    }

    public function addAggregations(EntityDefinition $definition, Criteria $criteria, Search $search, Context $context): void
    {
        $this->decorated->addAggregations($definition, $criteria, $search, $context);
    }

    public function setEnabled(bool $enabled): ElasticsearchHelper
    {
        return $this->decorated->setEnabled($enabled);
    }

    public function isSupported(EntityDefinition $definition): bool
    {
        return $this->decorated->isSupported($definition);
    }

    public function enabledMultilingualIndex(): bool
    {
        return $this->decorated->enabledMultilingualIndex();
    }

    private function isEsEnabled(string $salesChannelId): bool
    {
        $config = $this->configurationLoader->load($salesChannelId);

        return $config['esEnabled'];
    }
}

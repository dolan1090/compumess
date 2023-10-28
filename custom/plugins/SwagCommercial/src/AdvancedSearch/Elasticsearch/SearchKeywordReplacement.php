<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Elasticsearch;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\DataAbstractionLayer\SearchKeywordUpdater;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\ElasticsearchHelper;

#[Package('buyers-experience')]
class SearchKeywordReplacement extends SearchKeywordUpdater
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SearchKeywordUpdater $decorated,
        private readonly ElasticsearchHelper $helper
    ) {
    }

    /**
     * @param array<string> $ids
     */
    public function update(array $ids, Context $context): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            if ($this->helper->allowIndexing()) {
                return;
            }
        }

        $this->decorated->update($ids, $context);
    }

    public function reset(): void
    {
        $this->decorated->reset();
    }
}

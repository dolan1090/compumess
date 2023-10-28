<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Completion;

use OpenSearch\Client;
use OpenSearchDSL\Aggregation\Bucketing\FiltersAggregation;
use OpenSearchDSL\Aggregation\Bucketing\TermsAggregation;
use OpenSearchDSL\Query\TermLevel\PrefixQuery;
use OpenSearchDSL\Query\TermLevel\TermsQuery;
use OpenSearchDSL\Search;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition;
use Shopware\Elasticsearch\Framework\ElasticsearchHelper;

#[Package('buyers-experience')]
class CompletionSearcher
{
    private const COMPLETION_LIMIT = 10;

    /**
     * @param iterable<AbstractElasticsearchDefinition> $elasticsearchDefinitions
     *
     * @internal
     */
    public function __construct(
        private readonly Client $client,
        private readonly iterable $elasticsearchDefinitions,
        private readonly ElasticsearchHelper $elasticsearchHelper,
        private readonly string $timeout = '5s'
    ) {
    }

    /**
     * @return string[]
     */
    public function search(string $term): array
    {
        if (!License::get('ADVANCED_SEARCH-3068620')) {
            throw new LicenseExpiredException();
        }

        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return [];
        }

        if (trim($term) === '') {
            return [];
        }

        $parts = explode(' ', trim($term));

        $searchTerm = array_pop($parts);

        $searchArray = $this->getBody($searchTerm, $parts)->toArray();
        $searchArray['timeout'] = $this->timeout;

        $result = $this->client->search(
            [
                'index' => $this->getIndexAliases(),
                'body' => $searchArray,
            ]
        );

        if (!empty($result['aggregations']['autocompletion']['buckets'])) {
            $buckets = $result['aggregations']['autocompletion']['buckets'];
            if (\array_key_exists('autocompletion', $buckets) || \array_key_exists('autocompletionprefix', $buckets)) {
                $buckets = array_column($result['aggregations']['autocompletion']['buckets'], 'completion');
                $buckets = array_merge(...array_column($buckets, 'buckets'));
            }

            $autoCompletionResults = $buckets;
        } else {
            $autoCompletionResults = $result['aggregations']['autocompletion']['completion']['buckets'] ?? [];
        }

        $completion = [];
        $normalizedParts = array_map('mb_strtolower', $parts);

        foreach ($autoCompletionResults as $autoCompletionResult) {
            $resultWords = explode(' ', $autoCompletionResult['key']);

            $resultWords = array_filter($resultWords, function ($word) use ($normalizedParts) {
                return !\in_array(mb_strtolower($word), $normalizedParts, true);
            });

            $searchTerm = array_unique(array_merge($parts, $resultWords), \SORT_REGULAR);

            $completion[] = trim(implode(' ', $searchTerm));
        }

        return array_values(array_unique($completion));
    }

    /**
     * @param array<string> $parts
     */
    private function getBody(string $searchTerm, array $parts): Search
    {
        $search = new Search();
        $search->setSize(0);

        $parts = array_values(array_filter($parts, fn (string $part) => $part !== ''));

        $firstInclude = mb_substr($searchTerm, 0, 1);

        $include = '';

        foreach ($parts as $part) {
            $include .= '([' . mb_strtolower($part[0]) . mb_strtoupper($part[0]) . ']' . mb_substr($part, 1) . ' ){0,1}';
        }

        $include .= '[' . mb_strtolower($firstInclude) . mb_strtoupper($firstInclude) . ']' . mb_substr($searchTerm, 1) . '.*';

        $aggregation = new TermsAggregation('completion', 'completion');
        $aggregation->addParameter('size', self::COMPLETION_LIMIT);
        $aggregation->addParameter('include', $include);

        if (\count($parts)) {
            $filters = new FiltersAggregation('autocompletion', [
                'autocompletion' => new TermsQuery('completion', $parts),
                'autocompletionprefix' => new PrefixQuery(
                    'completion',
                    implode(' ', $parts) . ' ' . $searchTerm,
                    [
                        'case_insensitive' => true,
                    ]
                ),
            ]);

            $filters->addAggregation($aggregation);

            $search->addAggregation($filters);

            return $search;
        }

        $aggregation->setName('autocompletion');

        $search->addAggregation($aggregation);

        return $search;
    }

    /**
     * @return array<string>
     */
    private function getIndexAliases(): array
    {
        $aliases = [];

        foreach ($this->elasticsearchDefinitions as $definition) {
            $aliases[] = $this->elasticsearchHelper->getIndexName($definition->getEntityDefinition());
        }

        return $aliases;
    }
}

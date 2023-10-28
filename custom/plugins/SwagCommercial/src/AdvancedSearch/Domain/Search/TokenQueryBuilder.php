<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Search;

use OpenSearchDSL\BuilderInterface;
use OpenSearchDSL\Query\Compound\BoolQuery;
use OpenSearchDSL\Query\FullText\MatchPhrasePrefixQuery;
use OpenSearchDSL\Query\FullText\MatchQuery;
use OpenSearchDSL\Query\FullText\MultiMatchQuery;
use OpenSearchDSL\Query\Joining\NestedQuery;
use OpenSearchDSL\Query\TermLevel\WildcardQuery;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\CustomField\CustomFieldService;
use Shopware\Elasticsearch\Product\SearchFieldConfig;

#[Package('buyers-experience')]
class TokenQueryBuilder
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityDefinitionQueryHelper $helper,
        private readonly CustomFieldService $customFieldService
    ) {
    }

    public function build(EntityDefinition $definition, string $token, SearchFieldConfig $config, Context $context): BuilderInterface
    {
        $field = $this->helper->getField($config->getField(), $definition, $definition->getEntityName(), false);
        $real = $field instanceof TranslatedField ? EntityDefinitionQueryHelper::getTranslatedField($definition, $field) : $field;

        if ($config->isCustomField()) {
            $real = $this->customFieldService->getCustomField(str_replace('customFields.', '', $config->getField()));

            if ($real === null) {
                return new BoolQuery();
            }
        }

        $association = EntityDefinitionQueryHelper::getAssociationPath($config->getField(), $definition);
        $root = $association ? explode('.', $association)[0] : null;
        $isTextField = $real instanceof StringField || $real instanceof LongTextField || $real instanceof ListField;

        if ($field instanceof TranslatedField) {
            return $this->buildTranslatedFieldTokenQueries($token, $config, $context, $isTextField, $root);
        }

        if (!$isTextField) {
            return $this->buildNonTextTokenQuery($token, $config, $root);
        }

        $queries = $this->buildTextTokenQuery($config, $token);

        return $this->wrapNestedQuery($queries, $root);
    }

    /**
     * @param array<BuilderInterface> $queries
     */
    private function wrapNestedQuery(array $queries, ?string $root = null): BuilderInterface
    {
        $outputQuery = new BoolQuery();

        foreach ($queries as $query) {
            $outputQuery->add($query, BoolQuery::SHOULD);
        }

        if ($root) {
            return new NestedQuery($root, $outputQuery);
        }

        return $outputQuery;
    }

    private function buildTranslatedFieldTokenQueries(string $token, SearchFieldConfig $config, Context $context, bool $isTextField, ?string $root = null): BuilderInterface
    {
        if (\count($context->getLanguageIdChain()) === 1) {
            $searchField = self::buildTranslatedFieldName($config, $context->getLanguageId());
            $config = new SearchFieldConfig($searchField, $config->getRanking(), $config->tokenize());

            if (!$isTextField) {
                return $this->buildNonTextTokenQuery($token, $config, $root);
            }

            $queries = $this->buildTextTokenQuery($config, $token);

            return $this->wrapNestedQuery($queries, $root);
        }

        $multiMatchFields = [];
        $fuzzyMatchFields = [];
        $matchPhraseFields = [];
        $ngramFields = [];

        foreach ($context->getLanguageIdChain() as $languageId) {
            $searchField = self::buildTranslatedFieldName($config, $languageId, 'search');

            $multiMatchFields[] = $searchField;
            $matchPhraseFields[] = $searchField;

            if ($config->tokenize()) {
                $ngramField = self::buildTranslatedFieldName($config, $languageId, 'ngram');
                $fuzzyMatchFields[] = $searchField;
                $ngramFields[] = $ngramField;
            }
        }

        $queries = [
            new MultiMatchQuery($multiMatchFields, $token, [
                'type' => 'best_fields',
                'fuzziness' => 0,
                'lenient' => true,
                'boost' => $config->getRanking() * 5,
            ]),
        ];

        if (!$isTextField) {
            return $this->wrapNestedQuery($queries, $root);
        }

        $queries[] = new MultiMatchQuery($matchPhraseFields, $token, [
            'type' => 'phrase_prefix',
            'slop' => 5,
            'boost' => $config->getRanking(),
        ]);

        if (!$config->tokenize()) {
            return $this->wrapNestedQuery($queries, $root);
        }

        $queries[] = new MultiMatchQuery($fuzzyMatchFields, $token, [
            'type' => 'best_fields',
            'fuzziness' => 'auto',
            'boost' => $config->getRanking() * 3,
        ]);

        $queries[] = new MultiMatchQuery($ngramFields, $token, [
            'type' => 'phrase',
            'boost' => $config->getRanking(),
        ]);

        return $this->wrapNestedQuery($queries, $root);
    }

    /**
     * @return array<BuilderInterface>
     */
    private function buildTextTokenQuery(SearchFieldConfig $config, string $token): array
    {
        $queries = [];

        $searchField = $config->isCustomField() ? $config->getField() : $config->getField() . '.search';

        $queries[] = new MatchQuery($searchField, $token, ['boost' => 5 * $config->getRanking()]);
        $queries[] = new MatchPhrasePrefixQuery($searchField, $token, ['boost' => $config->getRanking(), 'slop' => 5]);

        if ($config->tokenize()) {
            $ngramField = $config->isCustomField() ? $config->getField() : $config->getField() . '.ngram';
            $queries[] = new WildcardQuery($searchField, '*' . $token . '*', ['boost' => $config->getRanking()]);
            $queries[] = new MatchQuery($searchField, $token, ['fuzziness' => 'auto', 'boost' => 3 * $config->getRanking()]);
            $queries[] = new MatchQuery($ngramField, $token, ['boost' => $config->getRanking()]);
        }

        return $queries;
    }

    private function buildNonTextTokenQuery(string $token, SearchFieldConfig $config, ?string $root = null): BuilderInterface
    {
        $queries = [];

        $queries[] = new MatchQuery($config->getField(), $token, ['boost' => 5 * $config->getRanking(), 'lenient' => true]);

        return $this->wrapNestedQuery($queries, $root);
    }

    private static function buildTranslatedFieldName(SearchFieldConfig $fieldConfig, string $languageId, ?string $suffix = null): string
    {
        if ($fieldConfig->isCustomField()) {
            $parts = explode('.', $fieldConfig->getField());

            return sprintf('%s.%s.%s', $parts[0], $languageId, $parts[1]);
        }

        if ($suffix === null) {
            return sprintf('%s.%s', $fieldConfig->getField(), $languageId);
        }

        return sprintf('%s.%s.%s', $fieldConfig->getField(), $languageId, $suffix);
    }
}

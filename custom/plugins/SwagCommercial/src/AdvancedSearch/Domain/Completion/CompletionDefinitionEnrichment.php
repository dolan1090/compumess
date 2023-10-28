<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Completion;

use Shopware\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('buyers-experience')]
class CompletionDefinitionEnrichment
{
    /**
     * @param array<string, array<string>> $completionMapping
     *
     * @internal
     */
    public function __construct(
        private readonly array $completionMapping
    ) {
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function enrichMapping(): array
    {
        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return [];
        }

        return [
            'completion' => [
                'type' => 'keyword',
                'normalizer' => 'completionNormalizer',
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $data
     *
     * @return array<string, array<string, mixed>>
     */
    public function enrichData(EntityDefinition $definition, array $data): array
    {
        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return $data;
        }

        $mapping = \array_key_exists($definition->getEntityName(), $this->completionMapping) ? $this->completionMapping[$definition->getEntityName()] : [];

        foreach ($data as $id => $item) {
            $completion = [];

            foreach ($item as $field => $value) {
                // if completion mapping is set, only fields from mapping are used to form the completion data
                if (!empty($mapping) && !\in_array($field, $mapping, true)) {
                    continue;
                }

                $real = $definition->getField($field);

                if ($real === null) {
                    continue;
                }

                if ($real instanceof TranslatedField) {
                    $real = EntityDefinitionQueryHelper::getTranslatedField($definition, $real);
                }

                if (!$real instanceof StringField) {
                    continue;
                }

                if (\is_array($value)) {
                    foreach ($value as $val) {
                        $completion[] = $val;
                    }

                    continue;
                }

                if (\is_string($value) || \is_float($value) || \is_int($value)) {
                    $completion[] = (string) $value;
                }
            }

            $data[$id]['completion'] = array_values(array_unique(array_filter(explode(' ', implode(' ', $completion)))));
        }

        return $data;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Configuration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
#[Package('buyers-experience')]
class ConfigurationLoader implements ResetInterface, EventSubscriberInterface
{
    /**
     * @var array<array{id:string, andLogic: bool, minSearchLength: int, esEnabled: bool, hitCount: array<string, array<string, int>>, searchableFields: array<string, array<array{tokenize: int, ranking: int, entity: string, field: string}>>}>|array{}
     */
    private array $config = [];

    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'advanced_search_config.written' => 'reset',
            'advanced_search_config_field.written' => 'reset',
        ];
    }

    /**
     * @return array{id: string, andLogic: bool, minSearchLength: int, esEnabled: bool, hitCount: array<string, array<string, int>>, searchableFields: array<string, array<array{tokenize: int, ranking: int, entity: string, field: string}>>}
     */
    public function load(string $salesChannelId): array
    {
        if (\array_key_exists($salesChannelId, $this->config)) {
            return $this->config[$salesChannelId];
        }

        $sql = <<<'SQL'
SELECT
    LOWER(HEX(advanced_search_config.id)) AS id,
    advanced_search_config.and_logic AS andLogic,
    advanced_search_config.min_search_length AS minSearchLength,
    advanced_search_config.es_enabled AS esEnabled,
    advanced_search_config.hit_count AS hitCount,
    CONCAT(
        '[',
        GROUP_CONCAT(DISTINCT
                JSON_OBJECT(
                    'field', advanced_search_config_field.field,
                    'entity', advanced_search_config_field.entity,
                    'tokenize', advanced_search_config_field.tokenize,
                    'ranking', advanced_search_config_field.ranking
                )
            ),
        ']'
    ) as searchableFields
    FROM advanced_search_config
    LEFT JOIN advanced_search_config_field ON advanced_search_config_field.config_id = advanced_search_config.id AND advanced_search_config_field.searchable = 1
    WHERE advanced_search_config.sales_channel_id = :salesChannelId
    GROUP BY advanced_search_config.id
SQL;

        /** @var array{id:string, andLogic: int, minSearchLength: int, esEnabled: int, hitCount: string, searchableFields: string} $config */
        $config = $this->connection->fetchAssociative(
            $sql,
            [
                'salesChannelId' => Uuid::fromHexToBytes($salesChannelId),
            ],
        );

        /** @var array<string, array<string, int>> $hitCount */
        $hitCount = json_decode($config['hitCount'], true);
        /** @var array<array{tokenize: int, ranking: int, entity: string, field: string}> $fields */
        $fields = json_decode($config['searchableFields'], true);
        $searchableFields = [];

        foreach ($fields as $field) {
            $searchableFields[$field['entity']] = $searchableFields[$field['entity']] ?? [];
            $searchableFields[$field['entity']][] = $field;
        }

        return $this->config[$salesChannelId] = [
            'id' => $config['id'],
            'searchableFields' => $searchableFields,
            'esEnabled' => (bool) $config['esEnabled'],
            'minSearchLength' => (int) $config['minSearchLength'],
            'andLogic' => (bool) $config['andLogic'],
            'hitCount' => $hitCount,
        ];
    }

    public function reset(): void
    {
        $this->config = [];
    }
}

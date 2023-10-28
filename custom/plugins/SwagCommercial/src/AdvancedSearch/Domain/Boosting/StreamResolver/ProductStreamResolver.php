<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolver;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\Boosting;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

#[Package('buyers-experience')]
class ProductStreamResolver extends AbstractEntityStreamResolver
{
    public const TYPE = 'product_stream';

    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @param array<array{id:string, productStreamId:string, entityStreamId:null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null}> $boostings
     *
     * @return array<Boosting>
     */
    public function resolve(array $boostings): array
    {
        $ids = array_column($boostings, 'productStreamId');

        if (empty($ids)) {
            return [];
        }

        $apiFilters = $this->getApiFilter($ids);

        $boostingCombination = [];
        foreach ($boostings as $entry) {
            /** @var array<array<string, mixed>>|null $filter */
            $filter = \array_key_exists($entry['productStreamId'], $apiFilters) ? json_decode($apiFilters[$entry['productStreamId']], true) : null;
            if (!\is_array($filter)) {
                continue;
            }

            $boostingCombination[] = new Boosting($entry['boost'], $filter[0]);
        }

        return $boostingCombination;
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function supports(string $entityName): bool
    {
        return $entityName === ProductDefinition::ENTITY_NAME;
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, string>
     */
    private function getApiFilter(array $ids): array
    {
        /** @var array<string, string> */
        return $this->connection->fetchAllKeyValue(
            'SELECT LOWER(HEX(id)) AS id, api_filter AS apiFilter FROM product_stream WHERE id in (:ids)',
            [
                'ids' => Uuid::fromHexToBytesList($ids),
            ],
            ['ids' => ArrayParameterType::STRING]
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse\WarehouseGroupWarehouseDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class WarehouseGroupWarehouseGenerator implements DemodataGeneratorInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityWriterInterface $writer,
        private readonly WarehouseGroupWarehouseDefinition $warehouseGroupWarehouseDefinition
    ) {
    }

    public function getDefinition(): string
    {
        return WarehouseGroupWarehouseDefinition::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function generate(int $limit, DemodataContext $context, array $options = []): void
    {
        if ($limit <= 0) {
            return;
        }

        $console = $context->getConsole();

        /** @var array<int, string> $warehouseIds */
        $warehouseIds = $this->connection->fetchFirstColumn('SELECT LOWER(HEX(id)) AS id FROM warehouse order by RAND() LIMIT ' . $limit);

        if (!$warehouseIds) {
            $console->error('No existing Warehouses found, skipping generation of WarehouseGroupWarehouses!');

            return;
        }

        /** @var array<int, string> $warehouseGroupIds */
        $warehouseGroupIds = $this->connection->fetchFirstColumn('SELECT LOWER(HEX(id)) AS id FROM warehouse_group order by RAND() LIMIT ' . $limit);

        if (!$warehouseGroupIds) {
            $console->error('No existing WarehouseGroups found, skipping generation of WarehouseGroupWarehouses!');

            return;
        }

        $possibleCombinations = \count($warehouseIds) * \count($warehouseGroupIds);

        if ($possibleCombinations < $limit) {
            $limit = $possibleCombinations;
            $console->warning('Maximum possible number of WarehouseGroupWarehouse associations is ' . $possibleCombinations . '! Limit was lowered accordingly.');
        }

        $console->progressStart($limit);
        $faker = $context->getFaker();
        $combinations = $this->getPossibleCombinations($warehouseIds, $warehouseGroupIds);
        $payload = [];

        for ($i = 0; $i < $limit; ++$i) {
            /** @var array{0: string, 1: string} $combination */
            $combination = array_pop($combinations);
            [$warehouseId, $warehouseGroupId] = $combination;

            $payload[] = [
                'warehouseId' => $warehouseId,
                'warehouseGroupId' => $warehouseGroupId,
                'priority' => $faker->randomNumber(2) * 10,
            ];
        }

        $writeContext = WriteContext::createFromContext($context->getContext());

        foreach (array_chunk($payload, 100) as $chunk) {
            $this->writer->upsert($this->warehouseGroupWarehouseDefinition, $chunk, $writeContext);
            $console->progressAdvance(\count($chunk));
        }

        $console->progressFinish();
    }

    /**
     * @param array<int, string> $warehouseIds
     * @param array<int, string> $warehouseGroupIds
     *
     * @return array<array{0: string, 1: string}>
     */
    private function getPossibleCombinations(array $warehouseIds, array $warehouseGroupIds): array
    {
        /** @var iterable<array<int, mixed>> $iterable */
        $iterable = $this->combinationsGenerator([$warehouseIds, $warehouseGroupIds]);
        /** @var array<array{0: string, 1: string}> $combinations */
        $combinations = [...$iterable];
        shuffle($combinations);

        return $combinations;
    }

    /**
     * @param array<int, array<int, mixed>> $arrays
     *
     * @return iterable<array<int, mixed>>
     */
    private function combinationsGenerator(array $arrays): iterable
    {
        if ($arrays === []) {
            yield [];

            return;
        }

        $head = array_shift($arrays);

        foreach ($head as $elem) {
            foreach ($this->combinationsGenerator($arrays) as $combination) {
                yield [$elem, ...$combination];
            }
        }
    }
}

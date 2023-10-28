<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup\ProductWarehouseGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class ProductWarehouseGroupGenerator implements DemodataGeneratorInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityWriterInterface $writer,
        private readonly ProductWarehouseGroupDefinition $productWarehouseGroupDefinition
    ) {
    }

    public function getDefinition(): string
    {
        return ProductWarehouseGroupDefinition::class;
    }

    public function generate(int $limit, DemodataContext $context, array $options = []): void
    {
        if ($limit <= 0) {
            return;
        }

        $console = $context->getConsole();

        /** @var array<int, array{id:string, version_id: string}> $products */
        $products = $this->connection->fetchAllAssociative('SELECT LOWER(HEX(id)) AS id, LOWER(HEX(version_id)) as version_id FROM product order by RAND() LIMIT ' . $limit);

        if (!$products) {
            $console->error('No existing Products found, skipping generation of ProductWarehouseGroups!');

            return;
        }

        /** @var array<int, string> $warehouseGroupIds */
        $warehouseGroupIds = $this->connection->fetchFirstColumn('SELECT LOWER(HEX(id)) AS id FROM warehouse_group order by RAND() LIMIT ' . $limit);

        if (!$warehouseGroupIds) {
            $console->error('No existing WarehouseGroups found, skipping generation of ProductWarehouseGroups!');

            return;
        }

        $possibleCombinations = \count($products) * \count($warehouseGroupIds);

        if ($possibleCombinations < $limit) {
            $limit = $possibleCombinations;
            $console->warning('Maximum possible number of ProductWarehouseGroup associations is ' . $possibleCombinations . '! Limit was lowered accordingly.');
        }

        $console->progressStart($limit);
        $combinations = $this->getPossibleCombinations($products, $warehouseGroupIds);
        $payload = [];

        for ($i = 0; $i < $limit; ++$i) {
            /** @var array{0: array{id:string, version_id: string}, 1: string} $combination */
            $combination = array_pop($combinations);
            [$product, $warehouseGroupId] = $combination;

            $payload[] = [
                'productId' => $product['id'],
                'productVersionId' => $product['version_id'],
                'warehouseGroupId' => $warehouseGroupId,
            ];
        }

        $writeContext = WriteContext::createFromContext($context->getContext());

        foreach (array_chunk($payload, 100) as $chunk) {
            $this->writer->upsert($this->productWarehouseGroupDefinition, $chunk, $writeContext);
            $context->getConsole()->progressAdvance(\count($chunk));
        }

        $context->getConsole()->progressFinish();
    }

    /**
     * @param array<int, array{id: string, version_id: string}> $products
     * @param array<int, string> $warehouseGroupIds
     *
     * @return array<array{0: array{id: string, version_id: string}, 1: string}>
     */
    private function getPossibleCombinations(array $products, array $warehouseGroupIds): array
    {
        /** @var iterable<array<int, mixed>> $iterable */
        $iterable = $this->combinationsGenerator([$products, $warehouseGroupIds]);
        /** @var array<array{0: array{id: string, version_id: string}, 1: string}> $combinations */
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

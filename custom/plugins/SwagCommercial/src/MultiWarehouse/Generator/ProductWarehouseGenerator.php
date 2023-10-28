<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse\ProductWarehouseDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class ProductWarehouseGenerator implements DemodataGeneratorInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityWriterInterface $writer,
        private readonly ProductWarehouseDefinition $productWarehouseDefinition
    ) {
    }

    public function getDefinition(): string
    {
        return ProductWarehouseDefinition::class;
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

        $sql = <<<'SQL'
            SELECT DISTINCT LOWER(HEX(product_warehouse_group.product_id)) as product_id,
                LOWER(HEX(product_warehouse_group.product_version_id)) as product_version_id,
                LOWER(HEX(warehouse_group_warehouse.warehouse_id)) as warehouse_id
            FROM product_warehouse_group
            INNER JOIN warehouse_group_warehouse
                ON product_warehouse_group.warehouse_group_id = warehouse_group_warehouse.warehouse_group_id
            LEFT JOIN product_warehouse
                ON product_warehouse.warehouse_id = warehouse_group_warehouse.warehouse_group_id
                AND product_warehouse.product_id = product_warehouse_group.product_id
            WHERE product_warehouse.product_id IS NULL
        SQL;

        $data = $this->connection->fetchAllAssociative($sql);

        if (empty($data)) {
            $console->error('No existing Warehouses found, skipping generation of ProductWarehouses!');

            return;
        }

        $console->progressStart($limit);
        $faker = $context->getFaker();

        $payload = [];
        foreach ($data as $ids) {
            $payload[] = [
                'productId' => $ids['product_id'],
                'productVersionId' => $ids['product_version_id'],
                'warehouseId' => $ids['warehouse_id'],
                'stock' => $faker->randomNumber(5),
            ];
        }

        $writeContext = WriteContext::createFromContext($context->getContext());

        foreach (array_chunk($payload, 100) as $chunk) {
            $this->writer->upsert($this->productWarehouseDefinition, $chunk, $writeContext);
            $console->progressAdvance(\count($chunk));
        }

        $console->progressFinish();
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('inventory')]
class WarehouseGroupGenerator implements DemodataGeneratorInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityWriterInterface $writer,
        private readonly WarehouseGroupDefinition $warehouseGroupDefinition,
        private readonly Connection $connection
    ) {
    }

    public function getDefinition(): string
    {
        return WarehouseGroupDefinition::class;
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
        $console->progressStart($limit);
        $ruleIds = $this->connection->fetchAllAssociative('SELECT LOWER(HEX(id)) as id FROM rule order by RAND() LIMIT ' . $limit);

        if (!$ruleIds) {
            $console->error('No existing Rules found, skipping generation of WarehouseGroups!');

            return;
        }

        $ruleIds = array_column($ruleIds, 'id');
        $faker = $context->getFaker();

        $payload = [];

        for ($i = 0; $i < $limit; ++$i) {
            $payload[] = [
                'id' => Uuid::randomHex(),
                'name' => $faker->format('productName') . ' Group',
                'priority' => $faker->randomNumber(2) * 10,
                'ruleId' => $faker->randomElement($ruleIds),
                'description' => random_int(0, 1) ? $faker->text() : null,
            ];
        }

        $writeContext = WriteContext::createFromContext($context->getContext());

        foreach (array_chunk($payload, 100) as $chunk) {
            $this->writer->upsert($this->warehouseGroupDefinition, $chunk, $writeContext);
            $console->progressAdvance(\count($chunk));
        }

        $console->progressFinish();
    }
}

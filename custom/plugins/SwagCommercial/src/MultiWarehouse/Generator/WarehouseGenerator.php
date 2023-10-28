<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Generator;

use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseDefinition;
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
class WarehouseGenerator implements DemodataGeneratorInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityWriterInterface $writer,
        private readonly WarehouseDefinition $warehouseDefinition
    ) {
    }

    public function getDefinition(): string
    {
        return WarehouseDefinition::class;
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
        $faker = $context->getFaker();

        $payload = [];
        for ($i = 0; $i < $limit; ++$i) {
            $payload[] = [
                'id' => Uuid::randomHex(),
                'name' => $faker->format('productName') . ' Warehouse',
                'description' => random_int(0, 1) ? $faker->text() : null,
            ];
        }

        $writeContext = WriteContext::createFromContext($context->getContext());

        foreach (array_chunk($payload, 100) as $chunk) {
            $this->writer->upsert($this->warehouseDefinition, $chunk, $writeContext);
            $console->progressAdvance(\count($chunk));
        }

        $console->progressFinish();
    }
}

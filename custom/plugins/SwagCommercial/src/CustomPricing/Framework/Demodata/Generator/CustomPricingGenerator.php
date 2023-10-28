<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Framework\Demodata\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Faker\Generator;
use Shopware\Commercial\CustomPricing\Domain\CustomPriceUpdater;
use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[Package('inventory')]
class CustomPricingGenerator implements DemodataGeneratorInterface
{
    private SymfonyStyle $io;

    private Generator $faker;

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly CustomPriceUpdater $customPriceUpdater
    ) {
    }

    public function getDefinition(): string
    {
        return CustomPriceDefinition::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function generate(int $numberOfItems, DemodataContext $context, array $options = []): void
    {
        if (!License::get('CUSTOM_PRICES-4458487')) {
            return;
        }
        $this->faker = $context->getFaker();
        $this->io = $context->getConsole();

        $this->createCustomPrices($numberOfItems);
    }

    private function createCustomPrices(int $numberOfItems): void
    {
        $this->io->progressStart($numberOfItems);

        $payload = [];

        $productIds = $this->getIds('product', $numberOfItems);
        $customerIds = $this->getIds('customer', 500);
        $customerGroupsIds = $this->getIds('customer_group', 10);
        $numberOfItems = min($numberOfItems, \count($productIds) * \count($customerIds));

        for ($i = 0; $i < $numberOfItems; ++$i) {
            /** @var string $productId */
            $productId = $this->faker->randomElement($productIds);
            /** @var array{customerGroupId?: string, customerId?: string} $customerIdentifier */
            $customerIdentifier = $this->faker->randomNumber(1) === 0
                ? ['customerGroupId' => $this->faker->randomElement($customerGroupsIds)]
                : ['customerId' => $this->faker->randomElement($customerIds)];

            $price = $this->faker->randomFloat(2, 1, 1000);

            $payload[] = [
                'productId' => $productId,
                'customerId' => $customerIdentifier['customerId'] ?? null,
                'customerGroupId' => $customerIdentifier['customerGroupId'] ?? null,
                'price' => [
                    [
                        'quantityStart' => 1,
                        'quantityEnd' => null,
                        'price' => [
                            [
                                'currencyId' => Defaults::CURRENCY,
                                'gross' => $price,
                                'net' => $price / 1.19,
                                'linked' => true,
                            ],
                        ],
                    ],
                ],
            ];
        }

        foreach (array_chunk($payload, 250) as $chunk) {
            $this->customPriceUpdater->uploadPrice($chunk);
            $this->io->progressAdvance(\count($chunk));
        }

        $this->io->progressFinish();
    }

    /**
     * @throws Exception
     *
     * @return array<int, mixed>
     */
    private function getIds(string $table, int $count): array
    {
        $ids = $this->connection
            ->fetchAllAssociative('SELECT LOWER(HEX(id)) AS id FROM ' . $table . ' order by RAND() LIMIT ' . $count);

        return \array_column($ids, 'id');
    }
}

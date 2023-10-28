<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Demodata\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionPlan\SubscriptionPlanDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionPlanGenerator implements DemodataGeneratorInterface
{
    /**
     * @param EntityRepository<SubscriptionPlanCollection> $planRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $planRepository,
    ) {
    }

    public function getDefinition(): string
    {
        return SubscriptionPlanDefinition::class;
    }

    public function generate(int $numberOfItems, DemodataContext $context, array $options = []): void
    {
        $context->getConsole()->progressStart($numberOfItems);

        $faker = $context->getFaker();

        $productIds = $this->getIds('product', 30);
        $intervalIds = $this->getIds('subscription_interval', 3);

        $payload = [];

        for ($i = 0; $i < $numberOfItems; ++$i) {
            $products = $faker->randomElements($productIds, $faker->numberBetween(1, \count($productIds)));
            $intervals = $faker->randomElements($intervalIds, $faker->numberBetween(1, \count($intervalIds)));

            $products = \array_map(function ($id) {
                return ['id' => $id];
            }, $products);

            $intervals = \array_map(function ($id) {
                return ['id' => $id];
            }, $intervals);

            $payload[] = [
                'id' => Uuid::randomHex(),
                'name' => $faker->format('productName'),
                'active' => true,
                'products' => $products,
                'subscriptionIntervals' => $intervals,
            ];

            $context->getConsole()->progressAdvance();
        }

        $this->planRepository->upsert($payload, $context->getContext());

        $context->getConsole()->progressFinish();
    }

    /**
     * @return array<int, string>
     */
    private function getIds(string $table, int $count = 1): array
    {
        /** @var string[] $result */
        $result = $this->connection->fetchFirstColumn('SELECT LOWER(HEX(id)) as id FROM ' . $table . ' LIMIT ' . $count);

        return $result;
    }
}

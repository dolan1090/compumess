<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Demodata\Generator;

use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalCollection;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalDefinition;
use Shopware\Commercial\Subscription\Framework\Demodata\Provider\CronIntervalProvider;
use Shopware\Commercial\Subscription\Framework\Demodata\Provider\DateIntervalProvider;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionIntervalGenerator implements DemodataGeneratorInterface
{
    /**
     * @param EntityRepository<SubscriptionIntervalCollection> $intervalRepository
     */
    public function __construct(
        private readonly EntityRepository $intervalRepository,
    ) {
    }

    public function getDefinition(): string
    {
        return SubscriptionIntervalDefinition::class;
    }

    public function generate(int $numberOfItems, DemodataContext $context, array $options = []): void
    {
        $context->getConsole()->progressStart($numberOfItems);

        $faker = $context->getFaker();
        $faker->addProvider(new CronIntervalProvider($faker));
        $faker->addProvider(new DateIntervalProvider($faker));

        $payload = [];

        for ($i = 0; $i < $numberOfItems; ++$i) {
            $intervalName = 'Interval';

            /** @var DateInterval $dateInterval */
            /** @phpstan-ignore-next-line phpstan does not know about this */
            $dateInterval = $faker->dateInterval();
            $intervalName .= \sprintf(' (Date: %s)', $dateInterval);

            /** @var CronInterval $cron */
            /** @phpstan-ignore-next-line phpstan does not know about this */
            $cron = $faker->cron();
            $intervalName .= \sprintf(' (Cron: %s)', $cron);

            $payload[] = [
                'id' => Uuid::randomHex(),
                'name' => $intervalName,
                'active' => $faker->boolean(80),
                'dateInterval' => $dateInterval,
                'cronInterval' => $cron,
            ];

            $context->getConsole()->progressAdvance();
        }

        $this->intervalRepository->upsert($payload, $context->getContext());

        $context->getConsole()->progressFinish();
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation;

use Psr\Log\LoggerInterface;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Commercial\Subscription\System\StateMachine\Subscription\State\SubscriptionStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal
 */
#[AsMessageHandler(handles: ScheduledSubscriptionsTask::class)]
#[Package('checkout')]
final class ScheduledSubscriptionsTaskHandler extends ScheduledTaskHandler
{
    /**
     * @param EntityRepository<SubscriptionCollection> $subscriptionRepository
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        private readonly EntityRepository $subscriptionRepository,
        private readonly MessageBusInterface $bus,
        private readonly LoggerInterface $logger,
        protected EntityRepository $scheduledTaskRepository,
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        try {
            $criteria = new Criteria();
            $criteria->addFilter(new RangeFilter('nextSchedule', [RangeFilter::LTE => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]));
            $criteria->addFilter(
                new EqualsAnyFilter('stateMachineState.technicalName', [
                    SubscriptionStates::STATE_ACTIVE,
                    SubscriptionStates::STATE_PAUSED,
                    SubscriptionStates::STATE_FLAGGED_CANCELLED,
                ])
            );

            /** @var string[] $subscriptionIds */
            $subscriptionIds = $this->subscriptionRepository->searchIds($criteria, Context::createDefaultContext())->getIds();

            foreach ($subscriptionIds as $subscriptionId) {
                $this->bus->dispatch(new GenerateSubscriptionOrder($subscriptionId));
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}

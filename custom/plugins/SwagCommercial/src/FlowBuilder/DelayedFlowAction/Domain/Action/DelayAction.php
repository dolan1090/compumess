<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\Action;

use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\ScheduledTask\DelayActionTask;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Content\Flow\Dispatching\Struct\ActionSequence;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Event\CustomerAware;
use Shopware\Core\Framework\Event\OrderAware;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('business-ops')]
class DelayAction extends FlowAction
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly EntityRepository $delayRepository
    ) {
    }

    public static function getName(): string
    {
        return 'action.delay';
    }

    public function requirements(): array
    {
        return [];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        if (!License::get('FLOW_BUILDER-8996729')) {
            return;
        }

        /** @var ActionSequence $currentSequence */
        $currentSequence = $flow->getFlowState()->currentSequence;

        if (!$this->canBeDelayed($flow, $currentSequence)) {
            $this->logger->info(
                "DelayAction is not delayed due to being configured not correctly, or there are no actions that need to be delayed:\n"
                . 'Flow id: ' . $flow->getFlowState()->flowId . "\n"
                . 'DelayAction id: ' . $currentSequence->sequenceId . "\n"
            );

            return;
        }

        $flow->getFlowState()->delayed = true;

        /** @var array<int, array<string, string|int>> $config */
        $config = $flow->getConfig()['delay'];
        $executionTime = $this->convertExecutionTime($config);

        $orderVersionId = ($flow->hasData(OrderAware::ORDER) && $flow->getData(OrderAware::ORDER) instanceof OrderEntity)
            ? $flow->getData(OrderAware::ORDER)->getVersionId()
            : null;

        $this->delayRepository->create(
            [
                [
                    'eventName' => $flow->getName(),
                    'orderId' => $flow->getData(OrderAware::ORDER_ID),
                    'delaySequenceId' => $currentSequence->sequenceId,
                    'orderVersionId' => $orderVersionId,
                    'customerId' => $flow->getData(CustomerAware::CUSTOMER_ID),
                    'flowId' => $flow->getFlowState()->flowId,
                    'stored' => $flow->stored(),
                    'executionTime' => $executionTime,
                ],
            ],
            $flow->getContext()
        );

        // Rescheduled task for next delay execution time
        $this->connection->executeStatement(
            'UPDATE scheduled_task SET next_execution_time = :executionTime WHERE scheduled_task_class = :class AND next_execution_time >= :executionTime',
            ['class' => DelayActionTask::class, 'executionTime' => $executionTime]
        );
    }

    private function canBeDelayed(StorableFlow $flow, ActionSequence $currentSequence): bool
    {
        if (empty($flow->getConfig()['delay'] ?? [])) {
            return false;
        }

        return (bool) $currentSequence->nextAction;
    }

    /**
     * @param array<int, array<string, string|int>> $config
     */
    private function convertExecutionTime(array $config): string
    {
        $executionTime = new \DateTimeImmutable();

        foreach ($config as $times) {
            [$type, $value] = \array_values($times);

            switch ($type) {
                case $type === 'hour':
                    $executionTime = $executionTime->modify('+' . $value . 'hours');

                    break;
                case $type === 'day':
                    $executionTime = $executionTime->modify('+' . $value . 'days');

                    break;
                case $type === 'week':
                    $days = (int) $value * 7;
                    $executionTime = $executionTime->modify('+' . $days . 'days');

                    break;
                case $type === 'month':
                    $executionTime = $executionTime->modify('+' . $value . 'month');

                    break;
                default:
                    break;
            }
        }

        return $executionTime->format(Defaults::STORAGE_DATE_TIME_FORMAT);
    }
}

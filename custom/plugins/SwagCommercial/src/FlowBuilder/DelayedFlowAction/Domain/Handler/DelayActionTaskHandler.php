<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\Handler;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\ScheduledTask\DelayActionTask;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('business-ops')]
#[AsMessageHandler(handles: DelayActionTask::class)]
final class DelayActionTaskHandler extends ScheduledTaskHandler
{
    /**
     * @internal
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly Connection $connection,
        private readonly DelayActionHandler $delayHandler
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $now = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $delayIds = $this->connection->fetchAllAssociativeIndexed(
            'SELECT lower(hex(id)) from swag_delay_action WHERE execution_time <= :now AND expired = 0',
            [
                'now' => $now,
            ]
        );

        if (empty($delayIds)) {
            return;
        }

        /** @var array<int, string> $ids */
        $ids = \array_keys($delayIds);

        if (!License::get('FLOW_BUILDER-8996729')) {
            // The Delayed Actions will not be executed without the appropriate Plan.
            // It will be set to expire, just execute it manually
            $this->connection->executeStatement(
                'UPDATE swag_delay_action SET expired = 1 WHERE id IN (:ids)',
                ['ids' => Uuid::fromHexToBytesList($ids)],
                ['ids' => ArrayParameterType::STRING]
            );

            return;
        }

        $this->delayHandler->handle($ids);
    }

    protected function rescheduleTask(ScheduledTask $task, ScheduledTaskEntity $taskEntity): void
    {
        $now = new \DateTimeImmutable();
        /** @var string|null $nextExecutionTime */
        $nextExecutionTime = $this->connection->fetchOne('SELECT execution_time FROM swag_delay_action WHERE expired = 0 ORDER BY execution_time ASC');
        if (!$nextExecutionTime) {
            parent::rescheduleTask($task, $taskEntity);

            return;
        }

        $nextExecutionTime = new \DateTimeImmutable($nextExecutionTime);
        if ($nextExecutionTime < $now) {
            $nextExecutionTime = $now;
        }

        // Rescheduled task for next delay execution time
        $this->scheduledTaskRepository->update([
            [
                'id' => $task->getTaskId(),
                'status' => ScheduledTaskDefinition::STATUS_SCHEDULED,
                'lastExecutionTime' => $now,
                'nextExecutionTime' => $nextExecutionTime,
            ],
        ], Context::createDefaultContext());
    }
}

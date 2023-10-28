<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\ScheduledTask;

use Shopware\Commercial\Licensing\Reporting\TurnoverReporter;
use Shopware\Commercial\Licensing\Reporting\TurnoverReportingClient;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[Package('merchant-services')]
#[AsMessageHandler(handles: ReportTurnoverTask::class)]
final class ReportTurnoverTaskHandler extends ScheduledTaskHandler
{
    public const CONFIG_KEY_LAST_TURNOVER_REPORT_DATE = 'swag.commercial.lastTurnoverReportDate';

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly TurnoverReporter $turnoverReporter,
        private readonly TurnoverReportingClient $client,
        private readonly SystemConfigService $systemConfigService
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $now = new \DateTimeImmutable('@' . time());
        $end = $this->getLastCompleteDay($now);
        $start = $this->getStartDate($end);

        $turnoverCollection = $this->turnoverReporter->collect($start, $end);
        $this->client->reportTurnover($turnoverCollection, Context::createDefaultContext());

        $this->systemConfigService->set(
            self::CONFIG_KEY_LAST_TURNOVER_REPORT_DATE,
            $now->format(Defaults::STORAGE_DATE_TIME_FORMAT)
        );
    }

    private function getLastCompleteDay(\DateTimeImmutable $until): \DateTimeImmutable
    {
        $datetime = $until->sub(new \DateInterval('P1D'));

        return new \DateTimeImmutable($datetime->format('Y-m-d'));
    }

    private function getLastExecutionDate(): ?\DateTimeImmutable
    {
        $lastExecutionDateString = $this->systemConfigService->getString(self::CONFIG_KEY_LAST_TURNOVER_REPORT_DATE);
        if (empty($lastExecutionDateString)) {
            return null;
        }

        return new \DateTimeImmutable($lastExecutionDateString);
    }

    private function getStartDate(\DateTimeImmutable $lastCompleteDay): \DateTimeImmutable
    {
        $startDate = $lastCompleteDay->sub(new \DateInterval('P13D'));

        $lastExecutionDate = $this->getLastExecutionDate();
        if ($lastExecutionDate !== null && $lastExecutionDate < $startDate) {
            return new \DateTimeImmutable($lastExecutionDate->format('Y-m-d'));
        }

        return $startDate;
    }
}

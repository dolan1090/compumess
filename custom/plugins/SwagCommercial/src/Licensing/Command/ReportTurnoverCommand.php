<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Command;

use Shopware\Commercial\Licensing\Reporting\TurnoverReporter;
use Shopware\Commercial\Licensing\Reporting\TurnoverReportingClient;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
#[AsCommand('commercial:report-turnover', 'Reports the turnover of the specified timeframe')]
class ReportTurnoverCommand extends Command
{
    public function __construct(
        private readonly TurnoverReporter $turnoverReporter,
        private readonly TurnoverReportingClient $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('start', InputArgument::REQUIRED, 'The first day of the timeframe [YYYY-MM-DD]')
            ->addArgument('end', InputArgument::REQUIRED, 'The last day of the timeframe [YYYY-MM-DD]')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only print the report without actually sending it')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        /** @var string $start */
        $start = $input->getArgument('start');
        $start = \DateTimeImmutable::createFromFormat('Y-m-d', $start, new \DateTimeZone('UTC'));

        if (!$start) {
            $io->error('Invalid start date');

            return self::FAILURE;
        }

        /** @var string $end */
        $end = $input->getArgument('end');
        $end = \DateTimeImmutable::createFromFormat('Y-m-d', $end, new \DateTimeZone('UTC'));

        if (!$end) {
            $io->error('Invalid end date');

            return self::FAILURE;
        }

        if ($start > $end) {
            $io->error('End date must be the same as or later than start date');

            return self::FAILURE;
        }

        $report = $this->turnoverReporter->collect($start, $end);

        if ($input->getOption('dry-run')) {
            $io->write(\json_encode($report, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->client->reportTurnover($report, Context::createDefaultContext());

        return self::SUCCESS;
    }
}

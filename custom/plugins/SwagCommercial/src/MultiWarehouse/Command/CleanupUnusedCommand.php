<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Command;

use Composer\Console\Input\InputOption;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Command\Helper\CleanupUnusedHelper;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[Package('inventory')]
#[AsCommand('multi-warehouse:cleanup-unused')]
class CleanupUnusedCommand extends Command
{
    public function __construct(
        private readonly CleanupUnusedHelper $cleanupUnusedHelper,
    ) {
        parent::__construct();

        $this->setDescription('Removes orphaned warehouses and groups');
        $this->addOption('warehouses', 'w', InputOption::VALUE_NONE, 'Deletes only warehouses');
        $this->addOption('groups', 'g', InputOption::VALUE_NONE, 'Deletes only warehouse groups');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            throw new LicenseExpiredException();
        }

        $runWarehouseCleanup = $input->getOption('warehouses');
        $runGroupCleanup = $input->getOption('groups');
        $runAll = $runWarehouseCleanup === $runGroupCleanup;

        $io = new ShopwareStyle($input, $output);
        $io->title('MultiWarehouse clean-up unused warehouses & warehouse groups');

        if ($runAll || $runWarehouseCleanup) {
            $warehouseCount = $this->cleanupUnusedHelper->cleanupWarehouses();
            if ($warehouseCount > 0) {
                $io->success(sprintf('Removed %s warehouse orphan(s)', $warehouseCount));
            } else {
                $io->success('No warehouses found to remove');
            }
        }

        if ($runAll || $runGroupCleanup) {
            $warehouseGroupCount = $this->cleanupUnusedHelper->cleanupWarehouseGroups();
            if ($warehouseGroupCount > 0) {
                $io->success(sprintf('Removed %s warehouse group orphan(s)', $warehouseGroupCount));
            } else {
                $io->success('No warehouse groups found to remove');
            }
        }

        return Command::SUCCESS;
    }
}

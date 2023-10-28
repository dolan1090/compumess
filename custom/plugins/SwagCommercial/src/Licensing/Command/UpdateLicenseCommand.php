<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Command;

use Shopware\Commercial\Licensing\LicenseUpdater;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
#[AsCommand('commercial:license:update', 'Update commercial license key')]
class UpdateLicenseCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(private readonly LicenseUpdater $licenseUpdater)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->licenseUpdater->sync();

        $io = new SymfonyStyle($input, $output);

        $io->success('License has been updated');

        return self::SUCCESS;
    }
}

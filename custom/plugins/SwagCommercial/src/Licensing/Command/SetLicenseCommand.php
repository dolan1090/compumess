<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Command;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[Package('merchant-services')]
#[AsCommand(name: 'commercial:license:set', description: 'Set commercial license host & key')]
final class SetLicenseCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(private SystemConfigService $systemConfigService)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addArgument('license-host', InputArgument::REQUIRED, 'The license host')
            ->addArgument('license-key', InputArgument::REQUIRED, 'The license key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $host */
        $host = $input->getArgument('license-host');
        /** @var string $key */
        $key = $input->getArgument('license-key');
        $this->systemConfigService->set(License::CONFIG_STORE_LICENSE_HOST, $host);
        $this->systemConfigService->set(License::CONFIG_STORE_LICENSE_KEY, $key);

        $io = new SymfonyStyle($input, $output);

        $io->success('License has been set');

        return self::SUCCESS;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Command;

use Shopware\Commercial\Licensing\Feature;
use Shopware\Commercial\Licensing\Features;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[Package('merchant-services')]
#[AsCommand(name: 'commercial:feature:list', description: 'List features included in your plan and their status')]
final class FeatureListCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(private Features $features)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!License::hasLicense()) {
            $io->warning('Your license is missing or invalid');

            return self::FAILURE;
        }

        $features = License::availableFeatures();

        $features = array_map(function (Feature $feature) {
            return [
                $feature->code,
                $feature->name,
                $feature->description,
                $this->features->isNotDisabled($feature->code) ? '<info>Enabled</>' : '<error>Disabled</>',
            ];
        }, $features);

        $io->info('All features included in your license:');

        $io->table(['Code', 'Name', 'Description', 'Status'], $features);

        return self::SUCCESS;
    }
}

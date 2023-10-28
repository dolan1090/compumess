<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Command;

use Shopware\Commercial\Licensing\Features;
use Shopware\Core\Framework\Log\Package;
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
#[AsCommand(name: 'commercial:feature:enable', description: 'Enable a feature which is included in your plan')]
final class FeatureEnableCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(private Features $features)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this
            ->addArgument('features', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The feature names to enable');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $featuresToEnable */
        $featuresToEnable = $input->getArgument('features');

        $this->features->enable(array_unique($featuresToEnable));

        $io = new SymfonyStyle($input, $output);

        $io->success('The following features were enabled: ' . implode(', ', $featuresToEnable));

        return self::SUCCESS;
    }
}

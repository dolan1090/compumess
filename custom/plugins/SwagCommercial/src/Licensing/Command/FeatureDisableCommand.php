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
#[AsCommand(name: 'commercial:feature:disable', description: 'Disable a feature which is included in your plan')]
final class FeatureDisableCommand extends Command
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
            ->addArgument('features', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The feature names to disable');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $featuresToDisable */
        $featuresToDisable = $input->getArgument('features');

        $this->features->disable(array_unique($featuresToDisable));

        $io = new SymfonyStyle($input, $output);

        $io->success('The following features were disabled: ' . implode(', ', $featuresToDisable));

        return self::SUCCESS;
    }
}

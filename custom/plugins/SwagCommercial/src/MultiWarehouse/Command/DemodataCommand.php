<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Command;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouse\ProductWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Product\Aggregate\ProductWarehouseGroup\ProductWarehouseGroupDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\Warehouse\WarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\Aggregate\WarehouseGroupWarehouse\WarehouseGroupWarehouseDefinition;
use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Demodata\DemodataRequest;
use Shopware\Core\Framework\Demodata\DemodataService;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[Package('inventory')]
#[AsCommand('multi-warehouse:demodata')]
class DemodataCommand extends Command
{
    private const DEFAULT_COUNTS = [
        'warehouse' => 10,
        'product-warehouse' => 150,
        'warehouse-group' => 5,
        'warehouse-group-warehouse' => 20,
        'product-warehouse-group' => 150,
    ];

    /**
     * @internal
     */
    public function __construct(
        private readonly DemodataService $demodataService
    ) {
        parent::__construct();

        $this->addOption('warehouse', null, InputOption::VALUE_OPTIONAL, 'Number of Warehouse entities to create.', self::DEFAULT_COUNTS['warehouse']);
        $this->addOption('warehouse-group', null, InputOption::VALUE_OPTIONAL, 'Number of WarehouseGroup entities to create.', self::DEFAULT_COUNTS['warehouse-group']);
        $this->addOption('warehouse-group-warehouse', null, InputOption::VALUE_OPTIONAL, 'Number of WarehouseGroupWarehouse entities to create.', self::DEFAULT_COUNTS['warehouse-group-warehouse']);
        $this->addOption('product-warehouse', null, InputOption::VALUE_OPTIONAL, 'Number of ProductWarehouse entities to create.', self::DEFAULT_COUNTS['product-warehouse']);
        $this->addOption('product-warehouse-group', null, InputOption::VALUE_OPTIONAL, 'Number of ProductWarehouseGroup entities to create.', self::DEFAULT_COUNTS['product-warehouse-group']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!License::get('MULTI_INVENTORY-3749997')) {
            throw new LicenseExpiredException();
        }

        $io = new ShopwareStyle($input, $output);
        $io->title('MultiWarehouse demo data generator');

        $context = Context::createDefaultContext();

        $request = new DemodataRequest();

        /** @var string $numberWarehouse */
        $numberWarehouse = $input->getOption('warehouse');
        /** @var string $numberWarehouseGroup */
        $numberWarehouseGroup = $input->getOption('warehouse-group');
        /** @var string $numberWarehouseGroupWarehouse */
        $numberWarehouseGroupWarehouse = $input->getOption('warehouse-group-warehouse');
        /** @var string $numberProductWarehouseGroup */
        $numberProductWarehouseGroup = $input->getOption('product-warehouse-group');
        /** @var string $numberProductWarehouse */
        $numberProductWarehouse = $input->getOption('product-warehouse');

        $request->add(WarehouseDefinition::class, (int) $numberWarehouse);
        $request->add(WarehouseGroupDefinition::class, (int) $numberWarehouseGroup);
        $request->add(WarehouseGroupWarehouseDefinition::class, (int) $numberWarehouseGroupWarehouse);
        $request->add(ProductWarehouseGroupDefinition::class, (int) $numberProductWarehouseGroup);
        $request->add(ProductWarehouseDefinition::class, (int) $numberProductWarehouse);

        $demoContext = $this->demodataService->generate($request, $context, $io);

        $io->table(
            ['Entity', 'Items', 'Time'],
            $demoContext->getTimings()
        );

        return Command::SUCCESS;
    }
}

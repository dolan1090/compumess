<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Profile\Shopware\Gateway\Api\Reader;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use Swag\CustomizedProducts\Profile\Shopware\Gateway\Api\Reader\OptionReader;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\Gateway\Reader\ReaderRegistry;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\EnvironmentReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\TableCountReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\Reader\TableReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Api\ShopwareApiGateway;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Connection\ConnectionFactory;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;

class OptionReaderTest extends TestCase
{
    use KernelTestBehaviour;

    private OptionReader $optionReader;

    private MigrationContextInterface $migrationContext;

    protected function setUp(): void
    {
        $connectionFactory = new ConnectionFactory();
        $this->optionReader = new OptionReader($connectionFactory);

        $this->migrationContext = new MigrationContext(
            new Shopware55Profile(),
            new SwagMigrationConnectionEntity(),
            '',
            new OptionDataSet(),
            0,
            250
        );

        $environmentReader = new EnvironmentReader($connectionFactory);
        $tableReader = new TableReader($connectionFactory);
        $tableCountReader = new TableCountReader($connectionFactory, new DummyLoggingService());
        $gateway = new ShopwareApiGateway(
            new ReaderRegistry([$this->optionReader]),
            $environmentReader,
            $tableReader,
            $tableCountReader,
            $this->getContainer()->get('currency.repository'),
            $this->getContainer()->get('language.repository')
        );

        $this->migrationContext->setGateway($gateway);
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->optionReader->supports($this->migrationContext);

        static::assertTrue($supportsDefinition);
    }
}

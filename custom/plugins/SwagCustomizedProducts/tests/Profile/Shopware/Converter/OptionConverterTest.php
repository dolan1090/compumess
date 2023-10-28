<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Profile\Shopware\Converter;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Profile\Shopware\Converter\OptionConverter;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;
use SwagMigrationAssistant\Test\Mock\Migration\Mapping\DummyMappingService;

class OptionConverterTest extends TestCase
{
    private OptionConverter $optionConverter;

    private DummyMappingService $mappingService;

    private DummyLoggingService $loggingService;

    private string $runId;

    private SwagMigrationConnectionEntity $connection;

    private MigrationContextInterface $migrationContext;

    private Context $context;

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
        $this->mappingService = new DummyMappingService();
        $this->loggingService = new DummyLoggingService();
        $this->optionConverter = new OptionConverter($this->mappingService, $this->loggingService);

        $this->runId = Uuid::randomHex();
        $this->connection = new SwagMigrationConnectionEntity();
        $this->connection->setId(Uuid::randomHex());
        $this->connection->setProfileName(Shopware55Profile::PROFILE_NAME);
        $this->connection->setGatewayName(ShopwareLocalGateway::GATEWAY_NAME);
        $this->connection->setName('shopware');

        $this->migrationContext = new MigrationContext(
            new Shopware55Profile(),
            $this->connection,
            $this->runId,
            new OptionDataSet(),
            0,
            250
        );

        $this->mappingService->getOrCreateMapping($this->connection->getId(), DefaultEntities::CURRENCY, 'EUR', Context::createDefaultContext(), null, [], Uuid::randomHex());

        $templateData = require __DIR__ . '/../../../fixtures/template_data.php';
        $this->mappingService->getOrCreateMapping($this->connection->getId(), TemplateDataSet::getEntity(), $templateData[0]['id'], Context::createDefaultContext(), null, [], Uuid::randomHex());
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->optionConverter->supports($this->migrationContext);

        static::assertTrue($supportsDefinition);
    }

    public function testConvertWithPercentageSurcharge(): void
    {
        $optionData = require __DIR__ . '/../../../fixtures/option_data.php';

        $convertResult = $this->optionConverter->convert($optionData[0], $this->context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNotNull($convertResult->getMappingUuid());
        static::assertNotNull($converted);
        static::assertArrayHasKey('id', $converted);
        static::assertArrayHasKey('tax', $converted);
        static::assertArrayHasKey('type', $converted);
        static::assertArrayHasKey('typeProperties', $converted);
        static::assertEquals(1, $converted['relativeSurcharge']);
        static::assertArrayHasKey('percentageSurcharge', $converted);
        static::assertEquals(10, $converted['percentageSurcharge']);
        static::assertSame(
            'Option 1',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['displayName']
        );
        static::assertSame(
            'This is option description',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['description']
        );
        static::assertCount(0, $this->loggingService->getLoggingArray());
    }

    public function testConvertWithNotPercentageSurcharge(): void
    {
        $optionData = require __DIR__ . '/../../../fixtures/option_data.php';

        $convertResult = $this->optionConverter->convert($optionData[1], $this->context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNotNull($convertResult->getMappingUuid());
        static::assertNotNull($converted);
        static::assertArrayHasKey('id', $converted);
        static::assertArrayHasKey('price', $converted);
        static::assertArrayHasKey('tax', $converted);
        static::assertArrayHasKey('type', $converted);
        static::assertArrayHasKey('typeProperties', $converted);
        static::assertEquals(0, $converted['relativeSurcharge']);
        static::assertSame(
            'Option 2',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['displayName']
        );
        static::assertSame(
            'This is option description',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['description']
        );
        static::assertCount(0, $this->loggingService->getLoggingArray());
    }
}

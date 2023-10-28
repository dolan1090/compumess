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
use Swag\CustomizedProducts\Profile\Shopware\Converter\ValueConverter;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\ValueDataSet;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;
use SwagMigrationAssistant\Test\Mock\Migration\Mapping\DummyMappingService;
use SwagMigrationAssistant\Test\Mock\Migration\Media\DummyMediaFileService;

class ValueConverterTest extends TestCase
{
    protected Context $context;

    private ValueConverter $valueConverter;

    private DummyMappingService $mappingService;

    private DummyLoggingService $loggingService;

    private string $runId;

    private SwagMigrationConnectionEntity $connection;

    private MigrationContextInterface $migrationContext;

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
        $this->mappingService = new DummyMappingService();
        $this->loggingService = new DummyLoggingService();
        $mediaFileService = new DummyMediaFileService();
        $this->valueConverter = new ValueConverter($this->mappingService, $this->loggingService, $mediaFileService);

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
            new ValueDataSet(),
            0,
            250
        );

        $this->mappingService->getOrCreateMapping($this->connection->getId(), DefaultEntities::CURRENCY, 'EUR', Context::createDefaultContext(), null, [], Uuid::randomHex());

        $optionData = require __DIR__ . '/../../../fixtures/option_data.php';
        $this->mappingService->getOrCreateMapping($this->connection->getId(), OptionDataSet::getEntity(), $optionData[0]['id'], Context::createDefaultContext(), null, [], Uuid::randomHex());
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->valueConverter->supports($this->migrationContext);

        static::assertTrue($supportsDefinition);
    }

    public function testConvertWithPercentageSurcharge(): void
    {
        $valueData = require __DIR__ . '/../../../fixtures/value_data.php';

        $convertResult = $this->valueConverter->convert($valueData[0], $this->context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNull($convertResult->getUnmapped());
        static::assertNotNull($convertResult->getMappingUuid());
        static::assertNotNull($converted);
        static::assertArrayHasKey('id', $converted);
        static::assertEquals(1, $converted['relativeSurcharge']);
        static::assertArrayHasKey('percentageSurcharge', $converted);
        static::assertEquals(10, $converted['percentageSurcharge']);
        static::assertSame(
            'Value 1',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['displayName']
        );
        static::assertCount(0, $this->loggingService->getLoggingArray());
    }

    public function testConvertWithNotPercentageSurcharge(): void
    {
        $valueData = require __DIR__ . '/../../../fixtures/value_data.php';

        $convertResult = $this->valueConverter->convert($valueData[1], $this->context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNull($convertResult->getUnmapped());
        static::assertNotNull($convertResult->getMappingUuid());
        static::assertNotNull($converted);
        static::assertArrayHasKey('id', $converted);
        static::assertArrayHasKey('price', $converted);
        static::assertArrayHasKey('tax', $converted);
        static::assertEquals(0, $converted['relativeSurcharge']);
        static::assertSame(
            'Value 2',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['displayName']
        );
        static::assertCount(0, $this->loggingService->getLoggingArray());
    }
}

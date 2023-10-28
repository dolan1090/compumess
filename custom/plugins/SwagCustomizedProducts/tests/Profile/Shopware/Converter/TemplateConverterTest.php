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
use Swag\CustomizedProducts\Profile\Shopware\Converter\TemplateConverter;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\MigrationContext;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware55\Shopware55Profile;
use SwagMigrationAssistant\Test\Mock\Migration\Logging\DummyLoggingService;
use SwagMigrationAssistant\Test\Mock\Migration\Mapping\DummyMappingService;
use SwagMigrationAssistant\Test\Mock\Migration\Media\DummyMediaFileService;

class TemplateConverterTest extends TestCase
{
    private DummyMappingService $mappingService;

    private DummyLoggingService $loggingService;

    private TemplateConverter $templateConverter;

    private Context $context;

    private string $runId;

    private SwagMigrationConnectionEntity $connection;

    private MigrationContext $migrationContext;

    protected function setUp(): void
    {
        $this->context = Context::createDefaultContext();
        $this->mappingService = new DummyMappingService();
        $this->loggingService = new DummyLoggingService();
        $mediaFileService = new DummyMediaFileService();

        $this->templateConverter = new TemplateConverter(
            $this->mappingService,
            $this->loggingService,
            $mediaFileService
        );

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
            new TemplateDataSet(),
            0,
            250
        );

        $this->mappingService->getOrCreateMapping($this->connection->getId(), DefaultEntities::CURRENCY, 'EUR', Context::createDefaultContext(), null, [], Uuid::randomHex());
    }

    public function testSupports(): void
    {
        $supportsDefinition = $this->templateConverter->supports($this->migrationContext);

        static::assertTrue($supportsDefinition);
    }

    public function testConvert(): void
    {
        $templateData = require __DIR__ . '/../../../fixtures/template_data.php';

        $convertResult = $this->templateConverter->convert($templateData[0], $this->context, $this->migrationContext);

        $converted = $convertResult->getConverted();

        static::assertNull($convertResult->getUnmapped());
        static::assertNotNull($convertResult->getMappingUuid());
        static::assertNotNull($converted);
        static::assertArrayHasKey('id', $converted);
        static::assertArrayHasKey('translations', $converted);
        static::assertSame(
            'Template 1',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['displayName']
        );
        static::assertSame(
            'This is a template description',
            $converted['translations'][DummyMappingService::DEFAULT_LANGUAGE_UUID]['description']
        );
        static::assertCount(0, $this->loggingService->getLoggingArray());
        static::assertSame($templateData[0]['media']['name'], $converted['media']['title']);
        static::assertSame($templateData[0]['media']['description'], $converted['media']['alt']);
    }
}

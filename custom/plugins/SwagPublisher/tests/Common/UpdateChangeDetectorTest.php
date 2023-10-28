<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\Common;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\Common\UpdateChangeContextExtension;
use SwagPublisher\Common\UpdateChangeDetector;

class UpdateChangeDetectorTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testAssertGetSubscribedEvents(): void
    {
        static::assertSame([
            PreWriteValidationEvent::class => 'detectChanges',
        ], UpdateChangeDetector::getSubscribedEvents());
    }

    public function testDetectChangesWithCustomFieldsChanged(): void
    {
        $uuId = Uuid::randomHex();

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->method('getDatabasePlatform')
            ->willReturn([]);

        // assert that only id is given and not the custom fields
        $connectionMock
            ->expects(static::once())
            ->method('fetchOne')
            ->with('SELECT 1 AS id FROM `cms_page` WHERE `id` = :id LIMIT 1', ['id' => $uuId])
            ->willReturn('1');

        $contextMock = $this->createMock(Context::class);
        $contextMock
            ->method('hasExtension')
            ->willReturn(true);
        $contextMock
            ->method('getExtension')
            ->willReturn(new UpdateChangeContextExtension());

        $updateCommandMock = $this->createMock(UpdateCommand::class);
        $updateCommandMock
            ->method('getDefinition')
            ->willReturn($this->getContainer()->get(CmsPageDefinition::class));
        $updateCommandMock
            ->method('getPrimaryKey')
            ->willReturn([]);

        // custom fields from other plugins
        $updateCommandMock
            ->method('getPayload')
            ->willReturn(['id' => $uuId, 'custom_fields' => 'value']);

        $preWriteValidationEventMock = $this->createMock(PreWriteValidationEvent::class);
        $preWriteValidationEventMock
            ->method('getCommands')
            ->willReturn([$updateCommandMock]);
        $preWriteValidationEventMock
            ->method('getContext')
            ->willReturn($contextMock);

        $updateChangeDetector = new UpdateChangeDetector($connectionMock, [CmsPageDefinition::class]);
        $updateChangeDetector->detectChanges($preWriteValidationEventMock);
    }
}

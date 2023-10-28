<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Template;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Version\VersionDefinition;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\ScheduledTask\CleanVersionsTaskHandler;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;

class TemplateCleanVersionsTest extends TestCase
{
    use ServicesTrait;

    private EntityRepository $templateRepository;

    private EntityRepository $versionRepository;

    private Connection $connection;

    private CleanVersionsTaskHandler $taskHandler;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        /** @var EntityRepository $templateRepository */
        $templateRepository = $container->get(\sprintf('%s.repository', TemplateDefinition::ENTITY_NAME));
        $this->templateRepository = $templateRepository;
        /** @var EntityRepository $versionRepository */
        $versionRepository = $container->get(\sprintf('%s.repository', VersionDefinition::ENTITY_NAME));
        $this->versionRepository = $versionRepository;
        $this->connection = $container->get(Connection::class);
        $this->taskHandler = $container->get(CleanVersionsTaskHandler::class);
    }

    public function testRunDeletesOlderVersion(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $this->createTemplate($templateId, $context);
        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $this->connection->update(
            'version',
            ['created_at' => '2020-01-01 01:00:00.000'],
            ['id' => Uuid::fromHexToBytes($versionId)]
        );

        static::assertTrue($this->doesTemplateVersionExist($templateId, $versionId, $context));
        static::assertTrue($this->doesVersionDataExist($versionId, $context));
        $this->taskHandler->run();
        static::assertFalse($this->doesTemplateVersionExist($templateId, $versionId, $context));
        static::assertFalse($this->doesVersionDataExist($versionId, $context));
    }

    public function testRunNotDeletesRecentVersion(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $this->createTemplate($templateId, $context);
        $versionId = $this->templateRepository->createVersion($templateId, $context);

        static::assertTrue($this->doesTemplateVersionExist($templateId, $versionId, $context));
        static::assertTrue($this->doesVersionDataExist($versionId, $context));
        $this->taskHandler->run();
        static::assertTrue($this->doesTemplateVersionExist($templateId, $versionId, $context));
        static::assertTrue($this->doesVersionDataExist($versionId, $context));
    }

    public function testRunDeletesUnknownVersion(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $this->createTemplate($templateId, $context);
        $versionId = $this->templateRepository->createVersion($templateId, $context);
        $this->versionRepository->delete([['id' => $versionId]], $context);

        static::assertTrue($this->doesTemplateVersionExist($templateId, $versionId, $context));
        static::assertFalse($this->doesVersionDataExist($versionId, $context));
        $this->taskHandler->run();
        static::assertFalse($this->doesTemplateVersionExist($templateId, $versionId, $context));
        static::assertFalse($this->doesVersionDataExist($versionId, $context));
    }

    public function testRunNotDeletesLiveVersion(): void
    {
        $templateId = Uuid::randomHex();
        $context = Context::createDefaultContext();
        $this->createTemplate($templateId, $context);
        $this->templateRepository->createVersion($templateId, $context);

        static::assertTrue($this->doesTemplateVersionExist($templateId, Defaults::LIVE_VERSION, $context));
        static::assertFalse($this->doesVersionDataExist(Defaults::LIVE_VERSION, $context));
        $this->taskHandler->run();
        static::assertTrue($this->doesTemplateVersionExist($templateId, Defaults::LIVE_VERSION, $context));
        static::assertFalse($this->doesVersionDataExist(Defaults::LIVE_VERSION, $context));
    }

    private function doesTemplateVersionExist(string $templateId, string $versionId, Context $context): bool
    {
        $versionContext = $context->createWithVersionId($versionId);

        /** @var TemplateEntity $entity */
        $entity = $this->templateRepository->search(new Criteria([$templateId]), $versionContext)->first();
        static::assertInstanceOf(TemplateEntity::class, $entity);

        return $entity->getVersionId() === $versionId;
    }

    private function doesVersionDataExist(string $versionId, Context $context): bool
    {
        return (bool) $this->versionRepository->searchIds(new Criteria([$versionId]), $context)->firstId();
    }
}

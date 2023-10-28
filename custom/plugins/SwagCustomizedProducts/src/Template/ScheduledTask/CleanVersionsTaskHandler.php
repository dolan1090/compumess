<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\ScheduledTask;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Version\VersionDefinition;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Template\TemplateDefinition;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CleanVersionsTask::class)]
class CleanVersionsTaskHandler extends ScheduledTaskHandler
{
    private const REMAIN_DAYS = 7;

    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly Connection $connection,
        private readonly EntityRepository $templateRepository,
        private readonly EntityRepository $versionRepository
    ) {
        parent::__construct($scheduledTaskRepository);
    }

    public function run(): void
    {
        $context = Context::createDefaultContext();

        $builder = $this->connection->createQueryBuilder();
        $query = $builder
            ->select(['template.id AS id', 'template.version_id AS versionId', 'version.created_at AS createdAt'])
            ->from(TemplateDefinition::ENTITY_NAME, 'template')
            ->leftJoin('template', VersionDefinition::ENTITY_NAME, 'version', 'version.id = template.version_id')
            ->where($builder->expr()->neq('template.version_id', ':default'))
            ->andWhere($builder->expr()->or(
                $builder->expr()->isNull('version.created_at'),
                $builder->expr()->lt('version.created_at', 'DATE_SUB(NOW(), INTERVAL :days DAY)')
            ))
            ->setParameter('default', Uuid::fromHexToBytes(Defaults::LIVE_VERSION))
            ->setParameter('days', self::REMAIN_DAYS)
            ->executeQuery();

        if (!$query instanceof Result) {
            return;
        }

        $rows = $query->fetchAllAssociative();

        foreach ($rows as $row) {
            $id = Uuid::fromBytesToHex($row['id']);
            $versionId = Uuid::fromBytesToHex($row['versionId']);

            if ($versionId === Defaults::LIVE_VERSION) {
                continue;
            }

            $versionContext = $context->createWithVersionId($versionId);

            $this->templateRepository->delete([['id' => $id]], $versionContext);
        }

        $this->versionRepository->delete(\array_map(static fn (array $row): array => ['id' => Uuid::fromBytesToHex($row['versionId'])], $rows), $context);
    }
}

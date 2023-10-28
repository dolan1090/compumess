<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CleanupTask::class)]
class CleanupHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $repository,
        private readonly Connection $connection,
        private readonly CleanupConfig $config
    ) {
        parent::__construct($repository);
    }

    public function run(): void
    {
        $pagePks = $this->connection
            ->fetchAllAssociative('SELECT cms_page_id FROM cms_page_activity GROUP BY cms_page_id;');

        foreach ($pagePks as $pagePk) {
            $pageId = $pagePk['cms_page_id'];

            if ($this->config->hasMaxLogEntriesPerPage()) {
                $this->cleanupLogEntries($pageId, $this->config->maxLogEntriesPerPage);
            }

            if ($this->config->hasMaxLogEntriesWithDetailsPerPage()) {
                $this->cleanupLogEntriesWithDetails($pageId, $this->config->maxLogEntriesWithDetailsPerPage);
            }
        }
    }

    private function cleanupLogEntries(string $pageId, int $maxLogEntriesPerPage): void
    {
        $idsResult = $this->createIdSelectStatement($maxLogEntriesPerPage, $pageId);

        while ($id = $idsResult->fetchOne()) {
            $this->connection->executeStatement('DELETE FROM cms_page_activity WHERE id = :id', ['id' => $id]);
        }
    }

    private function cleanupLogEntriesWithDetails(string $pageId, int $maxLogEntriesWithDetailsPerPage): void
    {
        $idsResult = $this->createIdSelectStatement($maxLogEntriesWithDetailsPerPage, $pageId);

        while ($id = $idsResult->fetchOne()) {
            $this->connection->executeStatement('UPDATE cms_page_activity SET details = NULL WHERE id = :id', ['id' => $id]);
        }
    }

    private function createIdSelectStatement(int $maxEntries, string $pageId): Result
    {
        $sql = \sprintf('SELECT id FROM cms_page_activity WHERE cms_page_id = :pageId ORDER BY created_at DESC LIMIT %u,%s', $maxEntries, \PHP_INT_MAX);

        $ids = $this->connection->prepare($sql);

        return $ids->executeQuery(['pageId' => $pageId]);
    }
}

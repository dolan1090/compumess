<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait RawDatabaseAccess
{
    public function fetchCmsRowCounts(): array
    {
        $tables = [
            'cms_block',
            'cms_page',
            'cms_page_activity',
            'cms_page_draft',
            'cms_page_translation',
            'cms_section',
            'cms_slot',
            'cms_slot_translation',
        ];

        $selects = [];

        foreach ($tables as $table) {
            $selects[] = \sprintf('(SELECT COUNT(*) FROM %s) AS %s_count', $table, $table);
        }

        $selects[] = '(SELECT COUNT(*) FROM cms_page_activity WHERE details IS NOT NULL) AS cms_page_activity_with_details_count';

        return $this->getContainer()
            ->get(Connection::class)
            ->fetchAssociative('SELECT ' . \implode(',', $selects));
    }

    public function fetchCmsVersionCounts(): array
    {
        $tables = [
            'cms_block',
            'cms_page',
            'cms_section',
            'cms_slot',
        ];

        $selects = [];

        foreach ($tables as $table) {
            $selects[] = \sprintf('(SELECT COUNT(*) FROM (SELECT version_id FROM %s GROUP BY version_id) inner%s) AS %s_count', $table, $table, $table);
        }

        return $this->getContainer()
            ->get(Connection::class)
            ->fetchAssociative('SELECT ' . \implode(',', $selects));
    }

    public function fetchActivityDetails(): array
    {
        return $this->getContainer()
            ->get(Connection::class)
            ->fetchAllAssociative('SELECT * FROM cms_page_activity ORDER BY created_at');
    }

    abstract protected static function getContainer(): ContainerInterface;
}

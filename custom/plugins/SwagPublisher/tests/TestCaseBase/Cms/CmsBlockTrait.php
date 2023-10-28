<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\TestCaseBase\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;

trait CmsBlockTrait
{
    protected static function assertBlocksCountFromSection(int $expectedCount, CmsSectionEntity $cmsSectionEntity): void
    {
        $blocks = self::getBlocksFromSection($cmsSectionEntity);

        self::assertCount($expectedCount, $blocks);
    }

    protected static function getBlocksFromSection(CmsSectionEntity $cmsSectionEntity): CmsBlockCollection
    {
        $blocks = $cmsSectionEntity->getBlocks();

        self::assertInstanceOf(CmsBlockCollection::class, $blocks);

        return $blocks;
    }
}

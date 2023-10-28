<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\TestCaseBase\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

trait CmsSlotTrait
{
    protected static function assertSlotsCountFromBlock(int $expectedCount, CmsBlockEntity $cmsBlockEntity): void
    {
        $slots = self::getSlotsFromBlock($cmsBlockEntity);

        self::assertCount($expectedCount, $slots);
    }

    protected static function getSlotsFromSection(CmsSectionEntity $cmsSectionEntity): CmsSlotCollection
    {
        $blocks = self::getBlocksFromSection($cmsSectionEntity);
        $slots = $blocks->getSlots();

        self::assertInstanceOf(CmsSlotCollection::class, $slots);

        return $slots;
    }

    protected static function getSlotsFromBlock(CmsBlockEntity $cmsBlockEntity): CmsSlotCollection
    {
        $slots = $cmsBlockEntity->getSlots();

        self::assertInstanceOf(CmsSlotCollection::class, $slots);

        return $slots;
    }

    protected static function getTranslationsFromSlot(CmsSlotEntity $cmsSlotEntity): EntityCollection
    {
        $translations = $cmsSlotEntity->getTranslations();

        self::assertInstanceOf(EntityCollection::class, $translations);

        return $translations;
    }
}

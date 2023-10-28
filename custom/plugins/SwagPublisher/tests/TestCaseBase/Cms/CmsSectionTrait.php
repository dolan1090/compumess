<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\TestCaseBase\Cms;

use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSection\CmsSectionEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;

trait CmsSectionTrait
{
    protected static function assertSectionsCountFromPage(int $expectedCount, CmsPageEntity $cmsPageEntity): void
    {
        $sections = self::getSectionsFromPage($cmsPageEntity);

        self::assertCount($expectedCount, $sections);
    }

    protected static function getSectionFromSections(CmsSectionCollection $cmsSectionCollection, string $sectionId): CmsSectionEntity
    {
        $section = self::getSectionFromSectionsNullable($cmsSectionCollection, $sectionId);

        self::assertInstanceOf(CmsSectionEntity::class, $section);

        return $section;
    }

    protected static function getSectionFromSectionsNullable(CmsSectionCollection $cmsSectionCollection, string $sectionId): ?CmsSectionEntity
    {
        return $cmsSectionCollection->get($sectionId);
    }

    protected static function getSectionsFromPage(CmsPageEntity $cmsPageEntity): CmsSectionCollection
    {
        $sections = self::getSectionsFromPageNullable($cmsPageEntity);

        self::assertInstanceOf(CmsSectionCollection::class, $sections);

        return $sections;
    }

    protected static function getSectionsFromPageNullable(CmsPageEntity $cmsPageEntity): ?CmsSectionCollection
    {
        return $cmsPageEntity->getSections();
    }

    protected static function getSectionFromPage(CmsPageEntity $cmsPageEntity, string $sectionId): CmsSectionEntity
    {
        $section = self::getSectionFromPageNullable($cmsPageEntity, $sectionId);

        self::assertInstanceOf(CmsSectionEntity::class, $section);

        return $section;
    }

    protected static function getSectionFromPageNullable(CmsPageEntity $cmsPageEntity, string $sectionId): ?CmsSectionEntity
    {
        $sections = self::getSectionsFromPageNullable($cmsPageEntity);

        if (!$sections) {
            return null;
        }

        return self::getSectionFromSectionsNullable($sections, $sectionId);
    }
}

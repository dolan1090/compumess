<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class CriteriaFactory
{
    public static function forDraftWithPageAndVersion(string $pageId, string $draftVersion): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new AndFilter([
            new EqualsFilter('pageId', $pageId),
            new EqualsFilter('draftVersion', $draftVersion),
        ]));

        return $criteria;
    }

    public static function forDraftWithVersion(string $draftVersion): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('draftVersion', $draftVersion));

        return $criteria;
    }

    public static function withIds(string ...$ids): Criteria
    {
        return new Criteria($ids);
    }

    public static function forPageBySectionId(string $sectionId): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('sections.id', $sectionId));

        return $criteria;
    }

    public static function forPageByBlockId(string $blockId): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('sections.blocks.id', $blockId));

        return $criteria;
    }

    public static function forPageBySlotId(string $slotId): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('sections.blocks.slots.id', $slotId));

        return $criteria;
    }

    public static function forPageWithVersion(string $versionId): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('versionId', $versionId));
        $criteria->setLimit(1);

        return $criteria;
    }

    public static function forActivityWithPageAndVersion(string $pageId, string $versionId): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new AndFilter([
            new EqualsFilter('pageId', $pageId),
            new EqualsFilter('draftVersion', $versionId),
        ]));

        return $criteria;
    }
}

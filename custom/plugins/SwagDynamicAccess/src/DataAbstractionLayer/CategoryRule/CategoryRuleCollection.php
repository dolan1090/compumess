<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\CategoryRule;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<CategoryRuleEntity>
 */
class CategoryRuleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CategoryRuleEntity::class;
    }
}

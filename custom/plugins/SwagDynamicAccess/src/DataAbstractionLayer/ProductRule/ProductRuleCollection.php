<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\ProductRule;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<ProductRuleEntity>
 */
class ProductRuleCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductRuleEntity::class;
    }
}

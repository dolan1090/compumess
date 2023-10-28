<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\DataAbstractionLayer\CategoryRule;

use Shopware\Core\Framework\Log\Package;

#[Package('content')]
class CategoryRuleEvents
{
    /**
     * @Event("Shopware\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent")
     */
    final public const CATEGORY_RULE_DELETED_EVENT = 'swag_dynamic_access_category_rule.deleted';
}

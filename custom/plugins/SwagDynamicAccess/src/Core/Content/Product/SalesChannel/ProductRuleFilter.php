<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Core\Content\Product\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Swag\DynamicAccess\DataAbstractionLayer\Extension\ProductExtension;

class ProductRuleFilter extends MultiFilter
{
    /**
     * @param string[] $ruleIds
     */
    public function __construct(array $ruleIds)
    {
        parent::__construct(MultiFilter::CONNECTION_OR, [
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsAnyFilter('parent.' . ProductExtension::RULE_EXTENSION . '.id', $ruleIds),
                new EqualsFilter(ProductExtension::RULE_EXTENSION . '.id', null),
            ]),
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsAnyFilter(ProductExtension::RULE_EXTENSION . '.id', $ruleIds),
            ]),
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('parent.' . ProductExtension::RULE_EXTENSION . '.id', null),
                new EqualsFilter(ProductExtension::RULE_EXTENSION . '.id', null),
            ]),
        ]);
    }
}

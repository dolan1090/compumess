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
use Swag\DynamicAccess\DataAbstractionLayer\Extension\LandingPageExtension;

class LandingPageRuleFilter extends MultiFilter
{
    /**
     * @param string[] $ruleIds
     */
    public function __construct(array $ruleIds)
    {
        parent::__construct(MultiFilter::CONNECTION_OR, [
            new EqualsAnyFilter(LandingPageExtension::RULE_EXTENSION . '.id', $ruleIds),
            new EqualsFilter(LandingPageExtension::RULE_EXTENSION . '.id', null),
        ]);
    }
}

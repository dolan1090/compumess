<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

abstract class AbstractTemplateOptionValueListRoute
{
    abstract public function getDecorated(): AbstractTemplateOptionValueListRoute;

    abstract public function load(Criteria $criteria, SalesChannelContext $context): TemplateOptionValueListResponse;
}

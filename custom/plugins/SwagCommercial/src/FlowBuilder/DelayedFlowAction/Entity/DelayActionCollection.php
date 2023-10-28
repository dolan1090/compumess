<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Entity;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<DelayActionEntity>
 */
#[Package('business-ops')]
class DelayActionCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'swag_delay_action_collection';
    }

    protected function getExpectedClass(): string
    {
        return DelayActionEntity::class;
    }
}

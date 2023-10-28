<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\WebhookFlowAction;

use Shopware\Commercial\CommercialBundle;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('business-ops')]
class WebhookFlowAction extends CommercialBundle
{
    public function describeFeatures(): array
    {
        return [];
    }
}

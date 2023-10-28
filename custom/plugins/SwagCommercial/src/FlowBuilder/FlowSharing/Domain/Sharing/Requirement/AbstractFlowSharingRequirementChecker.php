<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;

#[Package('business-ops')]
abstract class AbstractFlowSharingRequirementChecker
{
    abstract public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct;

    /**
     * @param array<string, string|array<int, array<string, string>>> $requirements
     *
     * @return array<string, string|array<int, string>>
     */
    public function checkRequirement(array $requirements, Context $context): array
    {
        return [];
    }
}

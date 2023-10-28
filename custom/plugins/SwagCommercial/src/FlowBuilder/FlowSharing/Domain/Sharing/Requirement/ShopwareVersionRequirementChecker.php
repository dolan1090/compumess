<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('business-ops')]
final class ShopwareVersionRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public const VALIDATOR_NAME = 'shopwareVersion';

    public function __construct(private string $shopwareVersion)
    {
    }

    public function checkRequirement(array $requirements, Context $context): array
    {
        if (!\array_key_exists(self::VALIDATOR_NAME, $requirements)) {
            return [];
        }

        /** @var string $requireVersion */
        $requireVersion = $requirements[self::VALIDATOR_NAME];

        if ($requireVersion === $this->shopwareVersion) {
            return [];
        }

        return [self::VALIDATOR_NAME => $requireVersion];
    }

    public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct
    {
        $data->addRequirement([self::VALIDATOR_NAME => $this->shopwareVersion]);

        return $data;
    }
}

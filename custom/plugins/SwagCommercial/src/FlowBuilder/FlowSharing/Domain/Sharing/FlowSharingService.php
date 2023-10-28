<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement\AbstractFlowSharingRequirementChecker;
use Shopware\Commercial\FlowBuilder\FlowSharing\Exception\InvalidFlowException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @final
 */
#[Package('business-ops')]
class FlowSharingService
{
    /**
     * @internal
     *
     * @param AbstractFlowSharingRequirementChecker[]|iterable $requirement
     */
    public function __construct(
        private iterable $requirement,
        private Connection $connection
    ) {
        $this->requirement = $requirement;
        $this->connection = $connection;
    }

    public function download(string $flowId, Context $context): FlowSharingStruct
    {
        if (!License::get('FLOW_BUILDER-1270531')) {
            throw new LicenseExpiredException();
        }

        $flow = $this->getFlow($flowId);

        $flowSharing = new FlowSharingStruct($flow);

        foreach ($this->requirement as $requirement) {
            $flowSharing = $requirement->collect($flowSharing, $context);
        }

        return $flowSharing;
    }

    /**
     * @param array<string, string|array<int, array<string, string>>> $requirements
     *
     * @return array<string, string|array<int|string, mixed>>
     */
    public function checkRequirements(array $requirements, Context $context): array
    {
        if (!License::get('FLOW_BUILDER-1270531')) {
            throw new LicenseExpiredException();
        }

        $result = [];

        foreach ($this->requirement as $requirement) {
            $result = [...$requirement->checkRequirement($requirements, $context), ...$result];
        }

        return array_filter($result);
    }

    /**
     * @return array<string, mixed>
     */
    private function getFlow(string $flowId): array
    {
        $flow = $this->getFlowData($flowId);

        if (empty($flow)) {
            throw new InvalidFlowException($flowId);
        }

        $flow['sequences'] = $this->getSequenceData($flowId);

        return $flow;
    }

    /**
     * @return array<string, mixed>
     */
    private function getFlowData(string $flowId): array
    {
        $flow = $this->connection->fetchAssociative('
            SELECT LOWER(HEX(`id`)) as `id`,
                `name`,
                `event_name` as `eventName`,
                `priority`,
                `invalid`,
                `active`,
                `description`,
                `custom_fields` as `customFields`
            FROM `flow`
            WHERE `id` = :id
        ', ['id' => Uuid::fromHexToBytes($flowId)]);

        return $flow ?: [];
    }

    /**
     * @return array<array<string, mixed>>
     */
    private function getSequenceData(string $flowId): array
    {
        $sequences = $this->connection->fetchAllAssociative('
            SELECT LOWER(HEX(`id`)) as `id`,
                LOWER(HEX(`parent_id`)) as `parentId`,
                LOWER(HEX(`rule_id`)) as `ruleId`,
                LOWER(HEX(`app_flow_action_id`)) as `appFlowActionId`,
                `display_group` as `displayGroup`,
                `position`,
                `action_name` as `actionName`,
                `config`,
                `true_case` as `trueCase`,
                `custom_fields` as `customFields`
            FROM `flow_sequence`
            WHERE `flow_id` = :id
        ', ['id' => Uuid::fromHexToBytes($flowId)]);

        $sequences = array_map(function ($sequence) {
            /** @var string $config */
            $config = $sequence['config'];

            if ($config) {
                $sequence['config'] = json_decode($config, true, 512, \JSON_THROW_ON_ERROR);
            }

            return $sequence;
        }, $sequences);

        return $sequences;
    }
}

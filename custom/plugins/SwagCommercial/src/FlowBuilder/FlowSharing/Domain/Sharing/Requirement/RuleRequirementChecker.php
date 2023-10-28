<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('business-ops')]
final class RuleRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public function __construct(private Connection $connection)
    {
    }

    public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct
    {
        $flowData = $data->getFlow();

        /** @var string $flowId */
        $flowId = $flowData['id'];

        $rules = $this->getRules($flowId);

        if (empty($rules)) {
            return $data;
        }

        $conditions = $this->getConditions(array_column($rules, 'id'));
        $conditions = FetchModeHelper::group($conditions);

        $ruleCollector = [];

        foreach ($rules as $rule) {
            /** @var string $ruleId */
            $ruleId = $rule['id'];

            $rule['conditions'] = $conditions[$ruleId] ?? [];
            $ruleCollector[$ruleId] = $rule;
        }

        $data->addData(RuleDefinition::ENTITY_NAME, $ruleCollector);

        return $data;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function getRules(string $flowId): array
    {
        /** @var array<int, array<string, string>> $rules */
        $rules = $this->connection->fetchAllAssociative('
            SELECT LOWER(HEX(`rule`.`id`)) as `id`,
                `rule`.`name`,
                `rule`.`description`,
                `rule`.`priority`,
                `rule`.`invalid`,
                `rule`.`module_types` as `moduleTypes`,
                `rule`.`custom_fields` as `customFields`
            FROM `rule`
            INNER JOIN `flow_sequence` ON `flow_sequence`.`rule_id` = `rule`.`id`
            WHERE `flow_sequence`.`flow_id` = :id
        ', ['id' => Uuid::fromHexToBytes($flowId)]);

        return $rules;
    }

    /**
     * @param array<string> $ruleIds
     *
     * @return array<array<string, mixed>>
     */
    private function getConditions(array $ruleIds): array
    {
        $conditions = $this->connection->fetchAllAssociative(
            '
                SELECT LOWER(HEX(`rule_id`)) as array_key,
                    LOWER(HEX(`id`)) as `id`,
                    LOWER(HEX(`rule_id`)) as `ruleId`,
                    LOWER(HEX(`script_id`)) as `scriptId`,
                    LOWER(HEX(`parent_id`)) as `parentId`,
                    `type`,
                    `value`,
                    `position`,
                    `custom_fields`
                FROM `rule_condition`
                WHERE `rule_id` IN (:ids)
            ',
            ['ids' => Uuid::fromHexToBytesList($ruleIds)],
            ['ids' => ArrayParameterType::STRING]
        );

        $conditions = array_map(function ($condition) {
            /** @var string $value */
            $value = $condition['value'];

            if ($value) {
                $condition['value'] = json_decode($value, true, 512, \JSON_THROW_ON_ERROR);
            }

            return $condition;
        }, $conditions);

        return $conditions;
    }
}

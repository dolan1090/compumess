<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Content\Flow\Dispatching\Action\SetOrderStateAction;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineState\StateMachineStateDefinition;

/**
 * @internal
 */
#[Package('business-ops')]
final class OrderStateRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public function __construct(private Connection $connection)
    {
    }

    public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct
    {
        $flowData = $data->getFlow();

        if (!isset($flowData['sequences'])) {
            return $data;
        }

        /** @var array<int, array<string, mixed>> $sequences */
        $sequences = $flowData['sequences'];

        $technicalNames = [];

        foreach ($sequences as $sequence) {
            if ($sequence['actionName'] !== SetOrderStateAction::getName()) {
                continue;
            }

            /** @var array<string, string>  $config */
            $config = $sequence['config'];

            if (\array_key_exists('order', $config)) {
                $technicalNames[] = $config['order'];
            }

            if (\array_key_exists('order_delivery', $config)) {
                $technicalNames[] = $config['order_delivery'];
            }

            if (\array_key_exists('order_transaction', $config)) {
                $technicalNames[] = $config['order_transaction'];
            }
        }

        $technicalNames = array_unique($technicalNames);

        if (!empty($technicalNames)) {
            $data->addReference(StateMachineStateDefinition::ENTITY_NAME, $this->getStateMachineStates($technicalNames));
        }

        return $data;
    }

    /**
     * @param array<string> $technicalNames
     *
     * @return array<string, array<string, mixed>>
     */
    private function getStateMachineStates(array $technicalNames): array
    {
        $data = $this->connection->fetchAllAssociative(
            '
                SELECT LOWER(HEX(`state_machine_state`.`id`)) as `array_key`,
                    LOWER(HEX(`state_machine_state`.`id`)) as `id`,
                    `state_machine_state_translation`.`name`,
                    `locale`.`code` as `locale`
                FROM `state_machine_state`
                LEFT JOIN `state_machine_state_translation` ON `state_machine_state`.`id` = `state_machine_state_translation`.`state_machine_state_id`
                LEFT JOIN `language` ON `language`.`id` = `state_machine_state_translation`.`language_id`
                LEFT JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
                WHERE `state_machine_state`.`technical_name` IN (:technicalNames)
            ',
            ['technicalNames' => $technicalNames],
            ['technicalNames' => ArrayParameterType::STRING]
        );

        return FetchModeHelper::group($data);
    }
}

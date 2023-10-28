<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\Returning\MailTemplateTypes;
use Shopware\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceDefinition;
use Shopware\Core\Content\Flow\Aggregate\FlowTemplate\FlowTemplateDefinition;
use Shopware\Core\Content\Flow\FlowDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @phpstan-type DefaultEventAction array{event_name: string, mail_template_type: string, mail_template_type_additional?: string, have_admin: bool}
 */
#[Package('checkout')]
class Migration1681888842ReturnManagement_AddFlowTemplatesForReturnCreatedAndReturnStatesChanged extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1681888842;
    }

    public function update(Connection $connection): void
    {
        $existingFlowTemplates = $this->getExistingFlowTemplates($connection);

        /** @var array<string, array<string, string>> $mailTemplates */
        $mailTemplates = $this->getDefaultMailTemplates($connection);
        $eventActions = $this->getDefaultEventActions();

        $flowTemplates = [];
        $flows = [];
        $flowSequences = [];
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        foreach ($eventActions as $eventAction) {
            /** @var string $eventName */
            $eventName = $eventAction['event_name'];
            $templateName = $this->getEventFullNameByEventName($eventName);

            if (\in_array($templateName, $existingFlowTemplates, true)) {
                continue;
            }

            $flowTemplate = [
                'id' => Uuid::randomBytes(),
                'name' => $templateName,
                'created_at' => $createdAt,
            ];

            $flowId = Uuid::randomBytes();
            $flows[] = [
                'id' => $flowId,
                'name' => $templateName,
                'event_name' => $eventName,
                'priority' => 1,
                'active' => 1,
                'invalid' => 0,
                'created_at' => $createdAt,
            ];

            /** @var string $mailTemplateType */
            $mailTemplateType = $eventAction['mail_template_type'];

            $config = !\array_key_exists($mailTemplateType, $mailTemplates) ? null
                : $this->getConfigData($mailTemplates[$mailTemplateType]);

            $sequences = [
                [
                    'id' => Uuid::randomHex(),
                    'actionName' => 'action.mail.send',
                    'config' => $config,
                    'parentId' => null,
                    'ruleId' => null,
                    'position' => 1,
                    'trueCase' => 0,
                    'displayGroup' => 1,
                ],
            ];

            $flowSequences[] = [
                'id' => Uuid::randomBytes(),
                'flow_id' => $flowId,
                'rule_id' => null,
                'parent_id' => null,
                'action_name' => 'action.mail.send',
                'position' => 1,
                'true_case' => 0,
                'display_group' => 1,
                'created_at' => $createdAt,
                'config' => json_encode($config),
            ];

            if ($eventAction['have_admin']) {
                /** @var string $mailTemplateTypeAdditional */
                $mailTemplateTypeAdditional = $eventAction['mail_template_type_additional'] ?? '';

                $haveAdmin = $eventAction['have_admin'];
                $additionalConfig = !\array_key_exists($mailTemplateTypeAdditional, $mailTemplates) ? null
                    : $this->getConfigData($mailTemplates[$mailTemplateTypeAdditional], $haveAdmin);
                $sequences[] = [
                    'id' => Uuid::randomHex(),
                    'actionName' => 'action.mail.send',
                    'config' => $additionalConfig,
                    'parentId' => null,
                    'ruleId' => null,
                    'position' => 1,
                    'trueCase' => 0,
                    'displayGroup' => 1,
                ];

                $flowSequences[] = [
                    'id' => Uuid::randomBytes(),
                    'flow_id' => $flowId,
                    'rule_id' => null,
                    'parent_id' => null,
                    'action_name' => 'action.mail.send',
                    'position' => 1,
                    'true_case' => 0,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'display_group' => 1,
                    'config' => json_encode($additionalConfig),
                ];
            }
            $flowTemplate['config'] = json_encode([
                'eventName' => $eventName,
                'description' => null,
                'customFields' => null,
                'sequences' => $sequences,
            ], \JSON_THROW_ON_ERROR);

            $flowTemplates[] = $flowTemplate;
        }

        $queue = new MultiInsertQueryQueue($connection);

        foreach ($flowTemplates as $flowTemplate) {
            $queue->addInsert(FlowTemplateDefinition::ENTITY_NAME, $flowTemplate);
        }

        foreach ($flows as $flow) {
            $queue->addInsert(FlowDefinition::ENTITY_NAME, $flow);
        }

        foreach ($flowSequences as $sequence) {
            $queue->addInsert(FlowSequenceDefinition::ENTITY_NAME, $sequence);
        }

        $queue->execute();

        $this->registerIndexer($connection, 'flow.indexer');
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    private function getDefaultMailTemplates(Connection $connection): array
    {
        $mailTemplates = $connection->fetchAllAssociative('
                SELECT LOWER(HEX(mail_template.id)) as mail_template_id,
                       LOWER(HEX(mail_template.mail_template_type_id)) as mail_template_type_id,
                       mail_template_type.technical_name
                FROM mail_template
                INNER JOIN mail_template_type ON mail_template_type.id = mail_template.mail_template_type_id
                WHERE mail_template.system_default = 1 and mail_template_type.technical_name LIKE :technicalName
        ', ['technicalName' => '%order_return%']);

        $result = [];
        foreach ($mailTemplates as $mailTemplate) {
            $result[$mailTemplate['technical_name']] = [
                'mail_template_id' => $mailTemplate['mail_template_id'],
                'mail_template_type_id' => $mailTemplate['mail_template_type_id'],
            ];
        }

        return $result;
    }

    /**
     * @param array<string, string> $mailTemplateData
     *
     * @return array<string, mixed>
     */
    private function getConfigData(array $mailTemplateData, ?bool $haveAdmin = false): array
    {
        $config = [];
        foreach ($mailTemplateData as $key => $value) {
            $key = lcfirst(implode('', array_map('ucfirst', explode('_', $key))));
            $config[$key] = $value;
        }

        $config['recipient'] = ['data' => [], 'type' => $haveAdmin ? 'admin' : 'default'];

        return $config;
    }

    /**
     * @return DefaultEventAction[]
     */
    private function getDefaultEventActions(): array
    {
        return [
            [
                'event_name' => 'checkout.order.return.created',
                'mail_template_type' => MailTemplateTypes::MAILTYPE_ORDER_RETURN_CREATED,
                'mail_template_type_additional' => MailTemplateTypes::MAILTYPE_ORDER_RETURN_CREATED_MERCHANT,
                'have_admin' => true,
            ],
            [
                'event_name' => 'state_enter.order_return.state.in_progress',
                'mail_template_type' => MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_IN_PROGRESS,
                'have_admin' => false,
            ],
            [
                'event_name' => 'state_enter.order_return.state.cancelled',
                'mail_template_type' => MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_CANCELLED,
                'have_admin' => false,
            ],
            [
                'event_name' => 'state_enter.order_return.state.done',
                'mail_template_type' => MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_COMPLETED,
                'have_admin' => false,
            ],
        ];
    }

    private function getEventFullNameByEventName(string $eventName): string
    {
        $listEventName = [
            'checkout.order.return.created' => 'Order return created',
            'state_enter.order_return.state.in_progress' => 'Order return enters status in progress',
            'state_enter.order_return.state.cancelled' => 'Order return enters status cancelled',
            'state_enter.order_return.state.done' => 'Order return enters status completed',
        ];

        if (\array_key_exists($eventName, $listEventName)) {
            return $listEventName[$eventName];
        }

        return $eventName;
    }

    /**
     * @return string[]
     */
    private function getExistingFlowTemplates(Connection $connection): array
    {
        /** @var string[] $flowTemplates */
        $flowTemplates = $connection->fetchFirstColumn('SELECT DISTINCT name FROM flow_template');

        return $flowTemplates;
    }
}

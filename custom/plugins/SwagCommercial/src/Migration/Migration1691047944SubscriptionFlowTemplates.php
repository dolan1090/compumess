<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
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
 * @phpstan-type DefaultEventAction array{event_name: string, mail_template_type: string|null, mail_template_type_additional?: string, name: string}
 */
#[Package('checkout')]
class Migration1691047944SubscriptionFlowTemplates extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1691047944;
    }

    public function update(Connection $connection): void
    {
        $existingFlowTemplates = $this->getExistingFlowTemplates($connection);
        $mailTemplates = $this->getDefaultMailTemplates($connection);
        $eventActions = self::getDefaultEventActions();

        $flowTemplates = [];
        $flows = [];
        $flowSequences = [];
        $createdAt = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        foreach ($eventActions as $eventAction) {
            $eventName = $eventAction['event_name'];

            if (\in_array($eventName, $existingFlowTemplates, true)) {
                continue;
            }

            $translatedName = $eventAction['name'];

            $flowTemplate = [
                'id' => Uuid::randomBytes(),
                'name' => $translatedName,
                'created_at' => $createdAt,
            ];

            $mailTemplateType = $eventAction['mail_template_type'];

            if ($mailTemplateType) {
                $flowId = Uuid::randomBytes();
                $flows[] = [
                    'id' => $flowId,
                    'name' => $translatedName,
                    'event_name' => $eventName,
                    'priority' => 1,
                    'active' => 1,
                    'invalid' => 0,
                    'created_at' => $createdAt,
                ];

                $config = \array_key_exists($mailTemplateType, $mailTemplates)
                    ? $this->getConfigData($mailTemplates[$mailTemplateType])
                    : [];

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
                    'display_group' => '1',
                    'created_at' => $createdAt,
                    'config' => \json_encode($config),
                ];
            }

            $flowTemplate['config'] = \json_encode([
                'eventName' => $eventName,
                'description' => null,
                'customFields' => null,
                'sequences' => $sequences ?? [],
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
    }

    /**
     * @return string[]
     */
    private function getExistingFlowTemplates(Connection $connection): array
    {
        /** @var string[] $flowTemplates */
        $flowTemplates = $connection->fetchFirstColumn('SELECT JSON_UNQUOTE(JSON_EXTRACT(config, \'$.eventName\')) as event_name FROM `flow_template`');

        return $flowTemplates;
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
        ', ['technicalName' => 'subscription%']);

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
     * @param array<string, mixed> $mailTemplateData
     *
     * @return array<string, mixed>
     */
    private function getConfigData(array $mailTemplateData): array
    {
        $config = [];
        foreach ($mailTemplateData as $key => $value) {
            $key = \lcfirst(\str_replace('_', '', \ucwords($key, '_')));
            $config[$key] = $value;
        }

        $config['recipient'] = ['data' => [], 'type' => 'default'];

        return $config;
    }

    /**
     * @return DefaultEventAction[]
     */
    private static function getDefaultEventActions(): array
    {
        return [
            [
                'event_name' => 'state_enter.subscription.state.flagged_cancelled',
                'mail_template_type' => null,
                'name' => 'Subscription flagged for cancellation',
            ],
            [
                'event_name' => 'state_enter.subscription.state.cancelled',
                'mail_template_type' => Migration1690961072SubscriptionMailTemplates::MAIL_TYPE_SUBSCRIPTION_CANCELLATION,
                'name' => 'Subscription cancelled',
            ],
            [
                'event_name' => 'state_enter.subscription.state.active',
                'mail_template_type' => Migration1690961072SubscriptionMailTemplates::MAIL_TYPE_SUBSCRIPTION_REACTIVATION,
                'name' => 'Subscription reactivated',
            ],
            [
                'event_name' => 'state_enter.subscription.state.paused',
                'mail_template_type' => Migration1690961072SubscriptionMailTemplates::MAIL_TYPE_SUBSCRIPTION_PAUSE,
                'name' => 'Subscription paused',
            ],
            [
                'event_name' => 'checkout.subscription.placed',
                'mail_template_type' => Migration1690961072SubscriptionMailTemplates::MAIL_TYPE_SUBSCRIPTION_CONFIRMATION,
                'name' => 'Subscription placed',
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration\EmployeeAccountStatusChangedEvent;
use Shopware\Core\Content\Flow\Aggregate\FlowTemplate\FlowTemplateDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1690264805CreateEmployeeStatusChangedMailFlow extends MigrationStep
{
    public const FLOW_NAME = 'B2B employee status changed';

    public function getCreationTimestamp(): int
    {
        return 1690264805;
    }

    public function update(Connection $connection): void
    {
        $templateTypeId = $connection->fetchOne('SELECT id FROM mail_template_type WHERE technical_name = :name', ['name' => Migration1689057417CreateEmployeeStatusChangeMailTemplate::TYPE]);

        /** @var string|null $templateId */
        $templateId = $connection->fetchOne('SELECT id FROM mail_template WHERE mail_template_type_id = :id', ['id' => $templateTypeId]) ?? null;

        $this->createFlow($connection, $templateId);
        $this->createFlowTemplate($connection, $templateId);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function createFlow(Connection $connection, ?string $mailTemplateId): void
    {
        $flowId = $connection->fetchOne('SELECT id FROM flow WHERE name = :name', ['name' => self::FLOW_NAME]);

        if ($flowId) {
            return;
        }

        $flowId = Uuid::randomBytes();

        $connection->insert(
            'flow',
            [
                'id' => $flowId,
                'name' => self::FLOW_NAME,
                'event_name' => EmployeeAccountStatusChangedEvent::EVENT_NAME,
                'active' => true,
                'payload' => null,
                'invalid' => 0,
                'custom_fields' => null,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        if ($mailTemplateId !== null) {
            $connection->insert(
                'flow_sequence',
                [
                    'id' => Uuid::randomBytes(),
                    'flow_id' => $flowId,
                    'rule_id' => null,
                    'parent_id' => null,
                    'action_name' => 'action.mail.send',
                    'position' => 1,
                    'true_case' => 0,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'config' => sprintf(
                        '{"replyTo": null, "recipient": {"data": [], "type": "default"}, "mailTemplateId": "%s", "documentTypeIds": []}',
                        Uuid::fromBytesToHex($mailTemplateId)
                    ),
                ]
            );
        }

        $this->registerIndexer($connection, 'flow.indexer');
    }

    private function createFlowTemplate(Connection $connection, ?string $mailTemplateId): void
    {
        $flowTemplateId = $connection->fetchOne('SELECT id FROM flow_template WHERE name = :name', ['name' => self::FLOW_NAME]);

        if ($flowTemplateId) {
            return;
        }

        $sequenceConfig = [];

        if ($mailTemplateId !== null) {
            $sequenceConfig[] = [
                'id' => Uuid::randomHex(),
                'actionName' => 'action.mail.send',
                'config' => sprintf(
                    '{"replyTo": null, "recipient": {"data": [], "type": "default"}, "mailTemplateId": "%s", "documentTypeIds": []}',
                    Uuid::fromBytesToHex($mailTemplateId)
                ),
                'parentId' => null,
                'ruleId' => null,
                'position' => 1,
                'trueCase' => 0,
                'displayGroup' => 1,
            ];
        }

        $connection->insert(FlowTemplateDefinition::ENTITY_NAME, [
            'id' => Uuid::randomBytes(),
            'name' => self::FLOW_NAME,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            'config' => json_encode([
                'eventName' => EmployeeAccountStatusChangedEvent::EVENT_NAME,
                'description' => null,
                'customFields' => null,
                'sequences' => $sequenceConfig,
            ]),
        ]);
    }
}

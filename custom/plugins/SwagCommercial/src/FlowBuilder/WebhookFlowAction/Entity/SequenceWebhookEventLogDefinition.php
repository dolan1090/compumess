<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\WebhookFlowAction\Entity;

use Shopware\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Webhook\EventLog\WebhookEventLogDefinition;

#[Package('business-ops')]
class SequenceWebhookEventLogDefinition extends MappingEntityDefinition
{
    public const ENTITY_NAME = 'swag_sequence_webhook_event_log';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('sequence_id', 'sequenceId', FlowSequenceDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('webhook_event_log_id', 'webhookEventLogId', WebhookEventLogDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('flowSequence', 'sequence_id', FlowSequenceDefinition::class, 'id', false),
            new ManyToOneAssociationField('webhookEventLog', 'webhook_event_log_id', WebhookEventLogDefinition::class, 'id', false),
        ]);
    }
}

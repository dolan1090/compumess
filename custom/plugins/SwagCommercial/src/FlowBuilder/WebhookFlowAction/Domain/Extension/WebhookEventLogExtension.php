<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\WebhookFlowAction\Domain\Extension;

use Shopware\Commercial\FlowBuilder\WebhookFlowAction\Entity\SequenceWebhookEventLogDefinition;
use Shopware\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Webhook\EventLog\WebhookEventLogDefinition;

#[Package('business-ops')]
class WebhookEventLogExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                'flowSequences',
                FlowSequenceDefinition::class,
                SequenceWebhookEventLogDefinition::class,
                'webhook_event_log_id',
                'sequence_id'
            ))->addFlags(new CascadeDelete()),
        );
    }

    public function getDefinitionClass(): string
    {
        return WebhookEventLogDefinition::class;
    }
}

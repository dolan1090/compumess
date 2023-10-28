<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\Extension;

use Shopware\Commercial\FlowBuilder\DelayedFlowAction\Entity\DelayActionDefinition;
use Shopware\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('business-ops')]
class FlowSequenceEntityExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return FlowSequenceDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('delayActions', DelayActionDefinition::class, 'delay_sequence_id', 'id'))->addFlags(new CascadeDelete())
        );
    }
}

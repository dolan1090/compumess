<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Extension;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MediaAiTagDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('administration')]
class MediaExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return MediaDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToOneAssociationField('mediaAiTag', 'id', 'media_id', MediaAiTagDefinition::class, false))->addFlags(new ApiAware(), new SetNullOnDelete())
        );
    }
}

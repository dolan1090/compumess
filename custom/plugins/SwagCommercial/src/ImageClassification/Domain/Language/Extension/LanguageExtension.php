<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Language\Extension;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\Aggregate\MediaAiTagTranslation\MediaAiTagTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageDefinition;

#[Package('administration')]
class LanguageExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            new OneToManyAssociationField('mediaAiTagTranslation', MediaAiTagTranslationDefinition::class, 'language_id')
        );
    }
}

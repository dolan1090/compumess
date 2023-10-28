<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\Aggregate\MediaAiTagTranslation;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\MediaAiTagDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('administration')]
class MediaAiTagTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'media_ai_tag_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return MediaAiTagTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return MediaAiTagTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return MediaAiTagDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new ListField('tags', 'tags'))->addFlags(new ApiAware()),
        ]);
    }
}

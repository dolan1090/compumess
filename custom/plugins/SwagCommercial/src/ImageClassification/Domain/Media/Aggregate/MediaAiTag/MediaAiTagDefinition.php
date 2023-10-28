<?php declare(strict_types=1);

namespace Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag;

use Shopware\Commercial\ImageClassification\Domain\Media\Aggregate\MediaAiTag\Aggregate\MediaAiTagTranslation\MediaAiTagTranslationDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\WriteProtected;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('administration')]
class MediaAiTagDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'media_ai_tag';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return MediaAiTagCollection::class;
    }

    public function getEntityClass(): string
    {
        return MediaAiTagEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new Required()),
            (new BoolField('needs_analysis', 'needsAnalysis'))->addFlags(new WriteProtected(Context::SYSTEM_SCOPE)),

            (new TranslatedField('tags'))->addFlags(new ApiAware()),
            (new OneToOneAssociationField('media', 'media_id', 'id', MediaDefinition::class, false))->addFlags(new ApiAware(), new CascadeDelete()),

            (new TranslationsAssociationField(MediaAiTagTranslationDefinition::class, 'media_ai_tag_id'))->addFlags(new ApiAware(), new Required()),
        ]);
    }
}

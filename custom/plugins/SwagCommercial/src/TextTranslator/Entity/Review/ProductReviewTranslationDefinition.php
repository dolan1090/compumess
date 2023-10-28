<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Entity\Review;

use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageDefinition;

#[Package('inventory')]
class ProductReviewTranslationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'product_review_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ProductReviewTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductReviewTranslationEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            (new FkField('review_id', 'reviewId', ProductReviewDefinition::class))->addFlags(new Required()),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new Required()),
            (new StringField('title', 'title'))->addFlags(new Required()),
            (new StringField('content', 'content'))->addFlags(new Required()),
            (new StringField('comment', 'comment'))->addFlags(),
            new ManyToOneAssociationField('review', 'review_id', ProductReviewDefinition::class, 'id'),
            new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class, 'id'),
        ]);
    }
}

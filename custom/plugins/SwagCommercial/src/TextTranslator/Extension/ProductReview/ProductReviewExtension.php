<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Extension\ProductReview;

use Shopware\Commercial\TextTranslator\Entity\Review\ProductReviewTranslationDefinition;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductReviewExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return ProductReviewDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(new OneToManyAssociationField('translations', ProductReviewTranslationDefinition::class, 'review_id'));
    }
}

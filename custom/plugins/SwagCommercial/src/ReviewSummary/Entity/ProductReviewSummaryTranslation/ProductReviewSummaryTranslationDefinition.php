<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Entity\ProductReviewSummaryTranslation;

use Shopware\Commercial\ReviewSummary\Entity\ProductReviewSummary\ProductReviewSummaryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductReviewSummaryTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'product_review_summary_translation';

    public function getEntityName(): string
    {
        return static::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductReviewSummaryTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductReviewSummaryTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return ProductReviewSummaryDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new LongTextField('summary', 'summary'))->addFlags(new ApiAware(), new AllowHtml()),
            (new BoolField('visible', 'visible'))->addFlags(new ApiAware()),
        ]);
    }
}

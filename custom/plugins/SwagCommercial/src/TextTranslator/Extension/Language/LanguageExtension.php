<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Extension\Language;

use Shopware\Commercial\TextTranslator\Entity\Review\ProductReviewTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageDefinition;

#[Package('inventory')]
class LanguageExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(new OneToManyAssociationField('productReviewTranslations', ProductReviewTranslationDefinition::class, 'language_id'));
    }
}

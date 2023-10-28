<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Extension;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReasonTranslation\OrderReturnLineItemReasonTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Language\LanguageDefinition;

/**
 * @final tag:v6.5.0
 *
 * @internal
 */
#[Package('checkout')]
class LanguageExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return LanguageDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('orderReturnLineItemReasonTranslations', OrderReturnLineItemReasonTranslationDefinition::class, 'language_id'))->addFlags(new CascadeDelete()),
        );
    }
}

<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionOperatorTranslation\TemplateExclusionOperatorTranslationDefinition;

class TemplateExclusionOperatorDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'swag_customized_products_template_exclusion_operator';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return TemplateExclusionOperatorCollection::class;
    }

    public function getEntityClass(): string
    {
        return TemplateExclusionOperatorEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new StringField('operator', 'operator'))->addFlags(new Required(), new ApiAware()),
            (new StringField('template_option_type', 'templateOptionType'))->addFlags(new Required(), new ApiAware()),
            (new TranslatedField('label'))->addFlags(new ApiAware()),

            (new OneToManyAssociationField(
                'templateExclusionConditions',
                TemplateExclusionConditionDefinition::class,
                'template_exclusion_operator_id'
            ))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(
                TemplateExclusionOperatorTranslationDefinition::class,
                'swag_customized_products_template_exclusion_operator_id'
            ))->addFlags(new ApiAware()),
        ]);
    }
}

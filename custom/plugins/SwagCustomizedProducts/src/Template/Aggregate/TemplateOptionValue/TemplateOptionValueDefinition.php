<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Tax\TaxDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionCondition\TemplateExclusionConditionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateExclusionConditionValues\TemplateExclusionConditionValuesDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\TemplateOptionDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValuePrice\TemplateOptionValuePriceDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValueTranslation\TemplateOptionValueTranslationDefinition;

class TemplateOptionValueDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'swag_customized_products_template_option_value';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateOptionValueEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateOptionValueCollection::class;
    }

    protected function getParentDefinitionClass(): ?string
    {
        return TemplateOptionDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            (new VersionField())->addFlags(new ApiAware()),
            (new FkField('template_option_id', 'templateOptionId', TemplateOptionDefinition::class))
                ->addFlags(new Required(), new ApiAware()),
            (new ReferenceVersionField(TemplateOptionDefinition::class, 'template_option_version_id'))
                ->addFlags(new Required(), new ApiAware()),

            (new JsonField('value', 'value'))->addFlags(new ApiAware()),
            (new TranslatedField('displayName'))
                ->addFlags(new SearchRanking(SearchRanking::LOW_SEARCH_RANKING), new ApiAware()),
            (new StringField('item_number', 'itemNumber'))->addFlags(new ApiAware()),
            (new BoolField('default', 'default'))->addFlags(new ApiAware()),
            (new BoolField('one_time_surcharge', 'oneTimeSurcharge'))->addFlags(new ApiAware()),
            (new BoolField('relative_surcharge', 'relativeSurcharge'))->addFlags(new ApiAware()),
            (new BoolField('advanced_surcharge', 'advancedSurcharge'))->addFlags(new ApiAware()),
            (new IntField('position', 'position'))->addFlags(new Required(), new ApiAware()),
            (new PriceField('price', 'price'))->addFlags(new ApiAware()),
            (new FloatField('percentage_surcharge', 'percentageSurcharge'))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(
                TemplateOptionValueTranslationDefinition::class,
                'swag_customized_products_template_option_value_id'
            ))->addFlags(new Required(), new ApiAware()),
            (new OneToManyAssociationField('prices', TemplateOptionValuePriceDefinition::class, 'template_option_value_id'))
                ->addFlags(new CascadeDelete(), new ApiAware()),
            (new ManyToOneAssociationField('templateOption', 'template_option_id', TemplateOptionDefinition::class))
                ->addFlags(new ApiAware()),
            (new FkField('tax_id', 'taxId', TaxDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class))->addFlags(new ApiAware()),
            (new ManyToManyAssociationField(
                'templateExclusionConditions',
                TemplateExclusionConditionDefinition::class,
                TemplateExclusionConditionValuesDefinition::class,
                'template_option_value_id',
                'template_exclusion_condition_id'
            ))->addFlags(new ApiAware()),
        ]);
    }
}

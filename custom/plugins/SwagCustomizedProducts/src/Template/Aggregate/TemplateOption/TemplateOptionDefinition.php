<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateOption;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
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
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionPrice\TemplateOptionPriceDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionTranslation\TemplateOptionTranslationDefinition;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOptionValue\TemplateOptionValueDefinition;
use Swag\CustomizedProducts\Template\TemplateDefinition;

class TemplateOptionDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'swag_customized_products_template_option';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return TemplateOptionCollection::class;
    }

    public function getEntityClass(): string
    {
        return TemplateOptionEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'required' => false,
        ];
    }

    protected function getParentDefinitionClass(): ?string
    {
        return TemplateDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required(), new ApiAware()),
            new VersionField(),
            (new ReferenceVersionField(TemplateDefinition::class, 'template_version_id'))
                ->addFlags(new Required(), new ApiAware()),
            (new StringField('type', 'type'))->addFlags(new Required(), new ApiAware()),
            (new TranslatedField('displayName'))
                ->addFlags(new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING), new ApiAware()),
            (new TranslatedField('description'))->addFlags(new ApiAware()),
            (new TranslatedField('placeholder'))->addFlags(new ApiAware()),
            (new JsonField('type_properties', 'typeProperties'))->addFlags(new ApiAware()),
            (new StringField('item_number', 'itemNumber'))->addFlags(new ApiAware()),
            (new BoolField('required', 'required'))->addFlags(new ApiAware()),
            (new BoolField('one_time_surcharge', 'oneTimeSurcharge'))->addFlags(new ApiAware()),
            (new BoolField('relative_surcharge', 'relativeSurcharge'))->addFlags(new ApiAware()),
            (new BoolField('advanced_surcharge', 'advancedSurcharge'))->addFlags(new ApiAware()),
            (new IntField('position', 'position'))->addFlags(new ApiAware()),
            (new PriceField('price', 'price'))->addFlags(new ApiAware()),
            (new JsonField('calculated_price', 'calculatedPrice'))->addFlags(new Runtime(), new ApiAware()),
            (new FloatField('percentage_surcharge', 'percentageSurcharge'))->addFlags(new ApiAware()),

            (new TranslationsAssociationField(
                TemplateOptionTranslationDefinition::class,
                'swag_customized_products_template_option_id'
            ))->addFlags(new Required(), new ApiAware()),

            (new OneToManyAssociationField('prices', TemplateOptionPriceDefinition::class, 'template_option_id'))
                ->addFlags(new CascadeDelete(), new ApiAware()),
            (new OneToManyAssociationField('values', TemplateOptionValueDefinition::class, 'template_option_id'))
                ->addFlags(new CascadeDelete(), new ApiAware()),
            (new OneToManyAssociationField(
                'templateExclusionConditions',
                TemplateExclusionConditionDefinition::class,
                'template_option_id'
            ))->addFlags(new CascadeDelete(), new ApiAware()),

            (new FkField('template_id', 'templateId', TemplateDefinition::class))
                ->addFlags(new Required(), new ApiAware()),
            (new ManyToOneAssociationField('template', 'template_id', TemplateDefinition::class))
                ->addFlags(new ApiAware()),
            (new FkField('tax_id', 'taxId', TaxDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class))->addFlags(new ApiAware()),
        ]);
    }
}

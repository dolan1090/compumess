<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\VersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationDefinition;

class TemplateConfigurationShareDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'swag_customized_products_template_configuration_share';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TemplateConfigurationShareEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TemplateConfigurationShareCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'oneTime' => false,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
            new VersionField(),
            (new ReferenceVersionField(TemplateConfigurationDefinition::class, 'template_configuration_version_id'))
                ->addFlags(new Required()),

            (new BoolField('one_time', 'oneTime'))->addFlags(new Required()),

            (new FkField('template_configuration_id', 'templateConfigurationId', TemplateConfigurationDefinition::class))
                ->addFlags(new Required()),
            new ManyToOneAssociationField(
                'templateConfiguration',
                'template_configuration_id',
                TemplateConfigurationDefinition::class
            ),
        ]);
    }
}

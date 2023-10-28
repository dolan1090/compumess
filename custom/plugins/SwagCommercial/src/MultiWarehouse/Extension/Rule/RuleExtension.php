<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Extension\Rule;

use Shopware\Commercial\MultiWarehouse\Entity\WarehouseGroup\WarehouseGroupDefinition;
use Shopware\Core\Content\Rule\RuleDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('inventory')]
class RuleExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return RuleDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                'warehouseGroup',
                WarehouseGroupDefinition::class,
                'rule_id',
                'id',
            ))->addFlags(new RestrictDelete(), new ApiAware())
        );
    }
}

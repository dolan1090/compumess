<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Extension;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\User\UserDefinition;

/**
 * @final tag:v6.5.0
 *
 * @internal
 */
#[Package('checkout')]
class UserDefinitionExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return UserDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('orderReturnsCreated', OrderReturnDefinition::class, 'created_by_id', 'id'))->addFlags(new SetNullOnDelete())
        );

        $collection->add(
            (new OneToManyAssociationField('orderReturnsUpdated', OrderReturnDefinition::class, 'updated_by_id', 'id'))->addFlags(new SetNullOnDelete())
        );
    }
}

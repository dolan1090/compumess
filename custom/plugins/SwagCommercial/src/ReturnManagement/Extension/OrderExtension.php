<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Extension;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @final tag:v6.5.0
 *
 * @internal
 */
#[Package('checkout')]
class OrderExtension extends EntityExtension
{
    public function getDefinitionClass(): string
    {
        return OrderDefinition::class;
    }

    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('returns', OrderReturnDefinition::class, 'order_id'))->addFlags(new ApiAware(), new CascadeDelete()),
        );
    }
}

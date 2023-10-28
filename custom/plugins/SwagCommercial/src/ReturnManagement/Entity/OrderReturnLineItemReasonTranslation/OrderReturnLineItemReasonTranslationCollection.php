<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReasonTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<OrderReturnLineItemReasonTranslationEntity>
 */
#[Package('checkout')]
class OrderReturnLineItemReasonTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'order_return_line_item_reason_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return OrderReturnLineItemReasonTranslationEntity::class;
    }
}

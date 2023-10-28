<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItemReason\OrderReturnLineItemReasonDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\Translations;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1658718914ReturnManagement_AddDefaultReasonForReturnLineItem extends MigrationStep
{
    use ImportTranslationsTrait;

    final public const DEFAULT_REASON_CONTENT_EN = 'Others';
    final public const DEFAULT_REASON_CONTENT_DE = 'Andere';

    public function getCreationTimestamp(): int
    {
        return 1658718914;
    }

    public function update(Connection $connection): void
    {
        $reason = [
            'id' => Uuid::randomBytes(),
            'reason_key' => OrderReturnLineItemReasonDefinition::DEFAULT_REASON_KEY,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        try {
            $connection->insert(OrderReturnLineItemReasonDefinition::ENTITY_NAME, $reason);
        } catch (UniqueConstraintViolationException) {
            // Already exists, skip translation insertion too
            return;
        }

        $translation = new Translations(
            [
                'order_return_line_item_reason_id' => $reason['id'],
                'content' => self::DEFAULT_REASON_CONTENT_DE,
            ],
            [
                'order_return_line_item_reason_id' => $reason['id'],
                'content' => self::DEFAULT_REASON_CONTENT_EN,
            ]
        );

        $this->importTranslation('order_return_line_item_reason_translation', $translation, $connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // TODO: Implement updateDestructive() method.
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Subscriber;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Product\Aggregate\ProductReview\ProductReviewDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class ProductReviewWrittenSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductReviewDefinition::ENTITY_NAME . '.written' => 'onProductReviewWritten',
        ];
    }

    public function onProductReviewWritten(EntityWrittenEvent $event): void
    {
        if (!License::get('REVIEW_TRANSLATOR-1649854')) {
            return;
        }

        $writeResults = array_filter(
            $event->getWriteResults(),
            fn ($writeResult) => $writeResult->getOperation() !== EntityWriteResult::OPERATION_INSERT
        );

        $ids = array_values(
            array_map(fn ($writeResult) => $writeResult->getPrimaryKey(), $writeResults)
        );

        if (empty($ids)) {
            return;
        }

        $this->connection->executeStatement(
            'DElETE FROM `product_review_translation` WHERE `review_id` IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids)],
            ['ids' => ArrayParameterType::STRING]
        );
    }
}

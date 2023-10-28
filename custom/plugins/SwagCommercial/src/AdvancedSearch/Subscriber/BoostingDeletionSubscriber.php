<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Subscriber;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\AdvancedSearch\Entity\Boosting\BoostingDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class BoostingDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeDeleteEvent::class => 'checkAndDeleteEntityStream',
        ];
    }

    public function checkAndDeleteEntityStream(BeforeDeleteEvent $event): void
    {
        /** @var array<string> $ids */
        $ids = $event->getIds(BoostingDefinition::ENTITY_NAME);

        if (empty($ids)) {
            return;
        }

        $idsToDelete = $this->getEntityStreamIdsFromBoostingIds($ids);

        if (!$idsToDelete) {
            return;
        }

        $this->connection->executeStatement(
            'DELETE FROM `advanced_search_entity_stream` WHERE `id` IN (:ids)',
            [
                'ids' => $idsToDelete,
            ],
            ['ids' => ArrayParameterType::STRING]
        );
    }

    /**
     * @param string[] $boostingIds
     *
     * @return array<int, mixed>
     */
    private function getEntityStreamIdsFromBoostingIds(array $boostingIds): array
    {
        return $this->connection->fetchFirstColumn(
            'SELECT `entity_stream_id`
            FROM `advanced_search_boosting`
            WHERE `id` IN (:boostingIds)
            AND `entity_stream_id` IS NOT NULL',
            [
                'boostingIds' => Uuid::fromHexToBytesList($boostingIds),
            ],
            ['boostingIds' => ArrayParameterType::STRING]
        );
    }
}

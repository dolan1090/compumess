<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting\EntityStream\Indexing;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\AdvancedSearch\Entity\EntityStream\EntityStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\IteratorFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\OffsetQuery;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidFilterQueryException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\SearchRequestException;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Parser\QueryStringParser;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Serializer\SerializerInterface;

#[Package('buyers-experience')]
class EntityStreamEntityIndexer extends EntityIndexer
{
    public function __construct(
        private readonly Connection $connection,
        private readonly SerializerInterface $serializer,
        private readonly IteratorFactory $iteratorFactory,
        private readonly EntityDefinition $entityStreamDefinition
    ) {
    }

    public function getName(): string
    {
        return $this->entityStreamDefinition->getEntityName() . '.entity_stream_indexer';
    }

    public function iterate(?array $offset): ?EntityIndexingMessage
    {
        $iterator = $this->iteratorFactory->createIterator(EntityStreamDefinition::ENTITY_NAME, $offset);

        $query = $iterator->getQuery();
        $query->where('type = :type');
        $query->setParameter('type', $this->entityStreamDefinition->getEntityName());

        $ids = $iterator->fetch();

        if (empty($ids)) {
            return null;
        }

        return new EntityStreamIndexingMessage(array_values($ids), $iterator->getOffset());
    }

    public function update(EntityWrittenContainerEvent $event): ?EntityIndexingMessage
    {
        $updates = $event->getPrimaryKeys(EntityStreamDefinition::ENTITY_NAME);

        if (!$updates) {
            return null;
        }

        return new EntityStreamIndexingMessage(array_values($updates), null, $event->getContext());
    }

    public function handle(EntityIndexingMessage $message): void
    {
        $ids = $message->getData();
        $ids = array_unique(array_filter($ids));

        if (empty($ids)) {
            return;
        }

        $filters = $this->fetchFiltersById($ids);

        $update = new RetryableQuery(
            $this->connection,
            $this->connection->prepare('UPDATE advanced_search_entity_stream SET api_filter = :serialized, invalid = :invalid WHERE id = :id')
        );

        foreach ($filters as $id => $filter) {
            $invalid = false;

            $serialized = null;

            try {
                $serialized = $this->buildPayload($filter);
            } catch (InvalidFilterQueryException|SearchRequestException) {
                $invalid = true;
            } finally {
                $update->execute([
                    'serialized' => $serialized,
                    'invalid' => (int) $invalid,
                    'id' => $id,
                ]);
            }
        }
    }

    public function getTotal(): int
    {
        $iterator = $this->iteratorFactory->createIterator(EntityStreamDefinition::ENTITY_NAME);

        $query = $iterator->getQuery();
        $query->where('type = :type');
        $query->setParameter('type', $this->entityStreamDefinition->getEntityName());

        $iterator = new OffsetQuery($query);

        return $iterator->fetchCount();
    }

    public function getDecorated(): EntityIndexer
    {
        throw new DecorationPatternException(static::class);
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array<array<string, mixed>>>
     */
    private function fetchFiltersById(array $ids): array
    {
        $bytes = Uuid::fromHexToBytesList($ids);

        $filters = $this->connection->fetchAllAssociative(
            'SELECT advanced_search_entity_stream_filter.entity_stream_id as array_key, advanced_search_entity_stream_filter.*
             FROM advanced_search_entity_stream_filter
             INNER JOIN advanced_search_entity_stream
             ON advanced_search_entity_stream.id = advanced_search_entity_stream_filter.entity_stream_id
             WHERE entity_stream_id IN (:ids)
             AND advanced_search_entity_stream.type = (:type)
             ORDER BY entity_stream_id',
            [
                'ids' => $bytes,
                'type' => $this->entityStreamDefinition->getEntityName(),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );

        return FetchModeHelper::group($filters);
    }

    /**
     * @param array<array<string, mixed>> $filter
     */
    private function buildPayload(array $filter): string
    {
        $nested = $this->buildNested($filter);

        $searchException = new SearchRequestException();
        $streamFilter = [];

        foreach ($nested as $value) {
            $parsed = QueryStringParser::fromArray($this->entityStreamDefinition, $value, $searchException);
            $streamFilter[] = QueryStringParser::toArray($parsed);
        }

        if ($searchException->getErrors()->current()) {
            throw $searchException;
        }

        return $this->serializer->serialize($streamFilter, 'json');
    }

    /**
     * @param array<array<string, mixed>> $entities
     *
     * @return array<array<string, mixed>>
     */
    private function buildNested(array $entities, ?string $parentId = null): array
    {
        $nested = [];
        foreach ($entities as $entity) {
            if ($entity['parent_id'] !== $parentId) {
                continue;
            }

            $parameters = $entity['parameters'];
            if ($parameters && \is_string($parameters)) {
                $decodedParameters = json_decode($parameters, true);
                if (json_last_error() === \JSON_ERROR_NONE) {
                    $entity['parameters'] = $decodedParameters;
                }
            }

            /** @var string $type */
            $type = $entity['type'];
            if ($this->isMultiFilter($type)) {
                /** @var string $entityId */
                $entityId = $entity['id'];
                $entity['queries'] = $this->buildNested($entities, $entityId);
            }

            $nested[] = $entity;
        }

        return $nested;
    }

    private function isMultiFilter(string $type): bool
    {
        return \in_array($type, ['multi', 'not'], true);
    }
}

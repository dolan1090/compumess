<?php
declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Domain;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Commercial\CustomPricing\Entity\Field\CustomPriceField;
use Shopware\Commercial\CustomPricing\Entity\FieldSerializer\CustomPriceFieldSerializer;
use Shopware\Commercial\CustomPricing\Exception\Domain\CustomPriceException;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexer;
use Shopware\Core\Content\Product\DataAbstractionLayer\ProductIndexingMessage;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Sync\SyncOperationResult;
use Shopware\Core\Framework\Api\Sync\SyncResult;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableTransaction;
use Shopware\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexer;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\WriteCommandQueue;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @phpstan-type CustomPriceUploadType array{id?: ?string, productId: string, customerId?: ?string, product_version_id?: ?string, customerGroupId?: ?string,
 *     price: array<int, array{quantityStart: int, quantityEnd: int|null,
 *      price: array<int, array{currencyId: string, gross: float, net: float, linked: bool}>}>}
 * @phpstan-type CustomPriceDeleteType array{productIds?: array<int, string>, customerIds?: array<string>, customerGroupIds?: array<string>}
 */
#[Package('inventory')]
class CustomPriceUpdater
{
    final public const PERMITTED_ACTIONS = [
        CustomPriceUpdater::ACTION_UPSERT,
        CustomPriceUpdater::ACTION_DELETE,
    ];
    final public const OPERATION_KEYS = [
        'action',
        'payload',
    ];
    private const ACTION_UPSERT = 'upsert';
    private const ACTION_DELETE = 'delete';

    private const BATCH_SIZE = 250;

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly CustomPriceUpdaterValidator $validator,
        private readonly MessageBusInterface $messageBus,
        private readonly EntityIndexer $productIndexer,
        private readonly CustomPriceFieldSerializer $customPriceFieldSerializer,
        private readonly CustomPriceDefinition $customPriceDefinition
    ) {
    }

    /**
     * @param array<int, array{action?: string, payload: list<CustomPriceUploadType|CustomPriceDeleteType>}> $operations
     */
    public function sync(array $operations): SyncResult
    {
        foreach ($operations as $i => $operation) {
            if (!isset($operation['action']) || !isset($operation['payload'])) {
                throw CustomPriceException::incorrectOperationKeys($i, $operation);
            }
        }

        $results = [];
        // Preserve original array indices in sorting
        \uasort($operations, [$this, 'orderActions']);

        /** @var array{action: string, payload: list<CustomPriceUploadType|CustomPriceDeleteType>} $operation */
        foreach ($operations as $i => $operation) {
            $result = null;

            switch ($operation['action']) {
                case self::ACTION_UPSERT:
                    /** @var list<CustomPriceUploadType> $operation */
                    $operation = $operation['payload'];
                    $result = $this->uploadPrice($operation);

                    break;
                case self::ACTION_DELETE:
                    /** @var list<CustomPriceDeleteType> $operation */
                    $operation = $operation['payload'];
                    $result = $this->delete($operation);

                    break;
                default:
                    throw CustomPriceException::invalidAction($operation['action']);
            }

            $results[$i] = $result->getResult();
        }
        // Revert to original ordering
        \ksort($results);

        return new SyncResult($results);
    }

    /**
     * @param list<CustomPriceUploadType> $prices
     */
    public function uploadPrice(array $prices): SyncOperationResult
    {
        try {
            $results = [];
            $errors = [];
            $multiInsertQueryQueue = new MultiInsertQueryQueue($this->connection, self::BATCH_SIZE, false, true);
            $createdAt = (new \DateTimeImmutable())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

            foreach ($prices as $index => $price) {
                if ($validationErrors = $this->validator->validateUpsert($price)) {
                    $errors[$index] = [
                        'entities' => [],
                        'errors' => [$validationErrors],
                    ];
                }

                if (empty($errors)) {
                    $id = Uuid::fromHexToBytes($price['id'] ?? Uuid::randomHex());
                    $versionId = Uuid::fromHexToBytes($price['product_version_id'] ?? Defaults::LIVE_VERSION);

                    $multiInsertQueryQueue->addInsert(CustomPriceDefinition::ENTITY_NAME, [
                        'id' => $id,
                        'product_id' => Uuid::fromHexToBytes($price['productId']),
                        'customer_id' => isset($price['customerId']) ? Uuid::fromHexToBytes($price['customerId']) : null,
                        'customer_group_id' => isset($price['customerGroupId']) ? Uuid::fromHexToBytes($price['customerGroupId']) : null,
                        'product_version_id' => $versionId,
                        'price' => $this->encodePrice($price['price']),
                        'created_at' => $createdAt,
                    ]);
                    $results[$index] = [
                        'entities' => [Uuid::fromBytesToHex($id)],
                        'errors' => [],
                    ];
                }
            }

            if (empty($errors)) {
                $multiInsertQueryQueue->execute();
                $this->indexProducts(\array_column($prices, 'productId'));
            }
        } catch (\Throwable $exception) {
            $errors = [
                [
                    'entities' => [],
                    'errors' => [$exception->getMessage()],
                ],
            ];
        }

        return new SyncOperationResult(empty($errors) ? $results : $errors);
    }

    /**
     * @param list<CustomPriceDeleteType> $operations
     */
    public function delete(array $operations): SyncOperationResult
    {
        $results = [];
        $errors = [];
        foreach ($operations as $index => $operation) {
            if ($validationErrors = $this->validator->validateDelete($operation)) {
                $errors[$index] = [
                    'entities' => [$operation],
                    'errors' => [$validationErrors],
                ];
            } else {
                $results[$index] = [
                    'entities' => [$operation],
                    'errors' => [],
                ];
            }
        }
        if (empty($errors)) {
            try {
                $this->transactionalDelete($operations);
            } catch (\Throwable $exception) {
                $errors = [
                    [
                        'entities' => [],
                        'errors' => [$exception->getMessage()],
                    ],
                ];
            }
        }

        return new SyncOperationResult($errors ?: $results);
    }

    /**
     * @param list<CustomPriceDeleteType> $operations
     */
    private function transactionalDelete(array $operations): void
    {
        RetryableTransaction::retryable($this->connection, function () use ($operations): void {
            foreach ($operations as $operation) {
                $query = $this->connection->createQueryBuilder()
                    ->delete(CustomPriceDefinition::ENTITY_NAME);

                if (!empty($operation['productIds'])) {
                    $query->andWhere('product_id IN (:productIds)');
                    $query->setParameter('productIds', Uuid::fromHexToBytesList($operation['productIds']), ArrayParameterType::STRING);
                }
                if (!empty($operation['customerIds'])) {
                    $query->andWhere('customer_id IN (:customerIds)');
                    $query->setParameter('customerIds', Uuid::fromHexToBytesList($operation['customerIds']), ArrayParameterType::STRING);
                }
                if (!empty($operation['customerGroupIds'])) {
                    $query->andWhere('customer_group_id IN (:customerGroupIds)');
                    $query->setParameter('customerGroupIds', Uuid::fromHexToBytesList($operation['customerGroupIds']), ArrayParameterType::STRING);
                }
                if (!empty($query->getParameters())) {
                    $this->connection->executeStatement(
                        $query->getSQL(),
                        $query->getParameters(),
                        $query->getParameterTypes()
                    );
                }
            }
        });
    }

    /**
     * @param array<int, string> $productIds
     */
    private function indexProducts(array $productIds): void
    {
        $message = new ProductIndexingMessage(\array_unique($productIds));
        $message->setIndexer('product.indexer');
        $skips = \array_diff($this->productIndexer->getOptions(), [ProductIndexer::INHERITANCE_UPDATER]);
        $message->addSkip(...$skips);
        $this->messageBus->dispatch($message);
    }

    /**
     * @param array{action: string, payload: array<int, array<string, mixed>>} $operation
     * @param array{action: string, payload: array<int, array<string, mixed>>} $other
     */
    private static function orderActions(array $operation, array $other): int
    {
        if ($operation['action'] === $other['action']) {
            return 0;
        }

        return $operation['action'] === self::ACTION_DELETE ? -1 : 1;
    }

    /**
     * @param array<mixed> $price
     */
    private function encodePrice(array $price): string
    {
        /** @var string $customPrice */
        $customPrice = $this->customPriceFieldSerializer->encode(
            new CustomPriceField('price', 'price'),
            new EntityExistence(null, [], true, false, false, []),
            new KeyValuePair('password', $price, true),
            new WriteParameterBag($this->customPriceDefinition, WriteContext::createFromContext(Context::createDefaultContext()), '', new WriteCommandQueue())
        )->current();

        return $customPrice;
    }
}

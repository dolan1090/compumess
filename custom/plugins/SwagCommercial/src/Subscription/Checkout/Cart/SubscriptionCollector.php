<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Checkout\Cart\Error\SubscriptionProductMappingError;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\Service\ResetInterface;

#[Package('checkout')]
class SubscriptionCollector implements CartDataCollectorInterface, ResetInterface
{
    /**
     * [planId => [productId => bool]]
     *
     * @var array<string, array<string, bool>>
     */
    private array $cache = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $context, CartBehavior $behavior): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        /** @var SubscriptionContextStruct|null $struct */
        $struct = $context->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);
        if ($struct === null) {
            return;
        }

        $planId = $struct->getPlan()->getId();
        $productIds = array_filter($original->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->getReferenceIds());

        if (!$productIds) {
            return;
        }

        $missingProductIds = $this->checkForMissingMappings($planId, $productIds);

        foreach ($missingProductIds as $productId) {
            $original->getErrors()->add(new SubscriptionProductMappingError(
                $productId,
                $planId,
            ));

            $lineItemsToRemove = $original->getLineItems()->filter(static function (LineItem $lineItem) use ($productId) {
                return $lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE && $lineItem->getReferencedId() === $productId;
            });

            foreach ($lineItemsToRemove as $lineItem) {
                $original->remove($lineItem->getId());
            }
        }
    }

    public function reset(): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $this->cache = [];
    }

    /**
     * @param string[] $productIds
     *
     * @return string[]
     */
    private function checkForMissingMappings(string $planId, array $productIds): array
    {
        if (!isset($this->cache[$planId]) || \count(array_diff($productIds, array_keys($this->cache[$planId])))) {
            $this->fetchProductMapping($planId, $productIds);
        }

        $missingProductIds = [];
        foreach ($productIds as $productId) {
            if (!$this->cache[$planId][$productId]) {
                $missingProductIds[] = $productId;
            }
        }

        return $missingProductIds;
    }

    /**
     * @param string[] $productIds
     */
    private function fetchProductMapping(string $planId, array $productIds): void
    {
        $sql = <<<SQL
SELECT LOWER(HEX(p.`id`))
FROM `subscription_plan_product_mapping` map
LEFT JOIN `product` p
    ON (map.`product_id` = p.`id` AND map.product_version_id = p.`version_id`)
        OR (map.`product_id` = p.`parent_id` AND map.product_version_id = p.`parent_version_id`)

WHERE map.`subscription_plan_id` = :planId
  AND p.`id` IN (:productIds)
  AND p.`version_id` = :productVersionId;
SQL;

        $verifiedProductIds = $this->connection->executeQuery($sql, [
            'planId' => Uuid::fromHexToBytes($planId),
            'productIds' => array_values(Uuid::fromHexToBytesList($productIds)),
            'productVersionId' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
        ], [
            'productIds' => ArrayParameterType::STRING,
        ])->fetchFirstColumn();

        foreach ($productIds as $productId) {
            $this->cache[$planId][$productId] = \in_array($productId, $verifiedProductIds, true);
        }
    }
}

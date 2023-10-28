<?php declare(strict_types=1);

namespace Shopware\Commercial\MultiWarehouse\Domain\Store;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\MultiWarehouse\Domain\Storage\StockStorage;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractAvailableCombinationLoader;
use Shopware\Core\Content\Product\SalesChannel\Detail\AvailableCombinationResult;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('inventory')]
class AvailableCombinationLoaderDecorator extends AbstractAvailableCombinationLoader
{
    public function __construct(
        private readonly AbstractAvailableCombinationLoader $decorated,
        private readonly StockStorage $storage,
        private readonly Connection $connection,
    ) {
    }

    public function getDecorated(): AbstractAvailableCombinationLoader
    {
        return $this->decorated;
    }

    public function loadCombinations(string $productId, SalesChannelContext $salesChannelContext): AvailableCombinationResult
    {
        $result = $this->decorated->loadCombinations($productId, $salesChannelContext);

        if (!License::get('MULTI_INVENTORY-3749997')) {
            return $result;
        }

        return $this->doLoad($result, $productId, $salesChannelContext->getContext(), $salesChannelContext->getSalesChannel()->getId());
    }

    /**
     * @deprecated tag:v6.6.0 - Method will be removed. Use `loadCombinations` instead.
     */
    public function load(string $productId, Context $context, string $salesChannelId): AvailableCombinationResult
    {
        Feature::triggerDeprecationOrThrow(
            'v6.6.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'loadCombinations')
        );

        $result = $this->decorated->load($productId, $context, $salesChannelId);

        if (!License::get('MULTI_INVENTORY-3749997')) {
            return $result;
        }

        return $this->doLoad($result, $productId, $context, $salesChannelId);
    }

    public function doLoad(AvailableCombinationResult $result, string $productId, Context $context, string $salesChannelId): AvailableCombinationResult
    {
        $variants = $this->fetchVariantData($productId, $context, $salesChannelId);

        if (!$variants) {
            return $result;
        }

        $stocks = $this->storage->load(array_keys($variants), $context);

        if (!$stocks) {
            return $result;
        }

        foreach ($stocks as $variantId => $stock) {
            $variant = $variants[$variantId] ?? null;

            if (!$variant) {
                continue;
            }

            $result->addCombination($variant['options'], $this->isAvailable($variant, $stock));
        }

        return $result;
    }

    /**
     * @param array{options: string[], min_purchase: int, purchase_steps: int} $variant
     */
    private function isAvailable(array $variant, int $stock): bool
    {
        return $stock >= $variant['min_purchase'] && $stock >= $variant['purchase_steps'];
    }

    /**
     * @return array<string, array{options: string[], min_purchase: int, purchase_steps: int}>
     */
    private function fetchVariantData(string $productId, Context $context, string $salesChannelId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->from('product');
        $query->leftJoin('product', 'product', 'parent', 'product.parent_id = parent.id');

        $query->andWhere('product.parent_id = :id');
        $query->andWhere('product.version_id = :versionId');
        $query->andWhere('IFNULL(product.active, parent.active) = :active');
        $query->andWhere('product.option_ids IS NOT NULL');
        $query->andWhere('IFNULL(product.is_closeout, parent.is_closeout) = 1');

        $query->setParameter('id', Uuid::fromHexToBytes($productId));
        $query->setParameter('versionId', Uuid::fromHexToBytes($context->getVersionId()));
        $query->setParameter('active', true);

        $query->innerJoin('product', 'product_visibility', 'visibilities', 'product.visibilities = visibilities.product_id');
        $query->andWhere('visibilities.sales_channel_id = :salesChannelId');
        $query->setParameter('salesChannelId', Uuid::fromHexToBytes($salesChannelId));

        $query->select([
            'LOWER(HEX(product.id)) as product_id',
            'product.option_ids as options',
            'IFNULL(product.min_purchase, parent.min_purchase) as min_purchase',
            'IFNULL(product.purchase_steps, parent.purchase_steps) as purchase_steps',
        ]);

        /** @var array<int, array<string, string>> $variants */
        $variants = $query->executeQuery()->fetchAllAssociative();

        $result = [];
        foreach ($variants as $variant) {
            /** @var string[] $options */
            $options = (array) json_decode($variant['options']);
            $result[$variant['product_id']] = [
                'options' => $options,
                'min_purchase' => (int) $variant['min_purchase'],
                'purchase_steps' => (int) $variant['purchase_steps'],
            ];
        }

        return $result;
    }
}

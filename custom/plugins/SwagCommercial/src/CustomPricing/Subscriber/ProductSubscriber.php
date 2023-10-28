<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Subscriber;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Commercial\CustomPricing\Entity\CustomPrice\Price\CustomPriceCollection;
use Shopware\Commercial\CustomPricing\Entity\Field\CustomPriceField;
use Shopware\Commercial\CustomPricing\Entity\FieldSerializer\CustomPriceFieldSerializer;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceEntity;
use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class ProductSubscriber implements EventSubscriberInterface
{
    public const CUSTOMER_PRICE_RULE = '9d7b6ee6d77547309bd35f92adfe1479';
    public const CUSTOM_PRICING_STATE = 'has-custom-pricing';

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly CustomPriceFieldSerializer $priceFieldSerializer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.' . ProductEvents::PRODUCT_LOADED_EVENT => ['salesChannelLoaded', 100],
        ];
    }

    public function salesChannelLoaded(SalesChannelEntityLoadedEvent $event): void
    {
        if (!License::get('CUSTOM_PRICES-4458487')) {
            return;
        }

        /** @var array<int, SalesChannelProductEntity> $products */
        $products = $event->getEntities();
        $salesChannelContext = $event->getSalesChannelContext();

        if (empty($products) || !$salesChannelContext->getCustomer()) {
            return;
        }

        $customPrices = $this->getCustomPrices($products, $salesChannelContext->getCustomer());

        if (\count($customPrices) === 0) {
            return;
        }

        $salesChannelContext->addState(static::CUSTOM_PRICING_STATE);

        $prices = [];
        foreach ($customPrices as $customPrice) {
            $prices[$customPrice['productId']] = $this->createPriceCollections($customPrice, $salesChannelContext);
        }

        foreach ($products as $product) {
            if (isset($prices[$product->getId()])) {
                $product->assign($prices[$product->getId()]);
            }
        }
    }

    /**
     * @param array{productId: string, price: array<CustomPriceCollection>|null} $customPrice
     *
     * @return array{price: PriceCollection|null, prices: ProductPriceCollection, cheapestPrice: null}
     */
    private function createPriceCollections(array $customPrice, SalesChannelContext $salesChannelContext): ?array
    {
        if ($customPrice['price'] === null) {
            return null;
        }

        $productId = $customPrice['productId'];
        $productPriceCollection = new ProductPriceCollection();

        foreach ($customPrice['price'] as $price) {
            if ($price->first() === null) {
                continue;
            }

            $start = $price->first()->getQuantityStart();
            $end = $price->first()->getQuantityEnd();

            $productPrice = new ProductPriceEntity();
            $productPrice->setId(Uuid::randomHex());
            $productPrice->setRuleId(self::CUSTOMER_PRICE_RULE);
            $productPrice->setPrice($price);
            $productPrice->setProductId($productId);
            $productPrice->setQuantityStart($start);
            $productPrice->setQuantityEnd($end);

            $productPriceCollection->add($productPrice);
        }
        $productPriceCollection->sortByQuantity();

        return [
            'price' => $productPriceCollection->first() !== null ? $productPriceCollection->first()->getPrice() : null,
            'prices' => $productPriceCollection,
            'cheapestPrice' => null,
        ];
    }

    /**
     * @param array<SalesChannelProductEntity> $products
     *
     * @return array<int, array{productId: string, price: array<CustomPriceCollection>|null}>
     */
    private function getCustomPrices(array $products, CustomerEntity $customer): array
    {
        $productIds = array_map(fn ($product) => Uuid::fromHexToBytes($product->getId()), $products);
        $tmpField = new CustomPriceField('price', 'price');

        $customerId = Uuid::fromHexToBytes($customer->getId());
        $customerGroupId = $customer->getGroupId() ? Uuid::fromHexToBytes($customer->getGroupId()) : null;

        /** @var array<int, array{productId: string, price: string|null}> $result */
        $result = $this->connection->createQueryBuilder()
            ->select('LOWER(HEX(product_id)) as productId', 'price')
            ->from(CustomPriceDefinition::ENTITY_NAME)
            ->where('product_id IN (:productIds)')
            ->andWhere('(customer_id = :customerId OR customer_group_id = :groupId)')
            ->setParameter('productIds', $productIds, ArrayParameterType::STRING)
            ->setParameter('customerId', $customerId)
            ->setParameter('groupId', $customerGroupId)
            ->orderBy('product_id', 'ASC')
            ->addOrderBy('customer_id', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return \array_map(fn ($item) => [
            'productId' => $item['productId'],
            'price' => $this->priceFieldSerializer->decode($tmpField, $item['price']),
        ], $result);
    }
}

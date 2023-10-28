<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Demodata\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionCollection;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\SubscriptionInterval\SubscriptionIntervalEntity;
use Shopware\Commercial\Subscription\Framework\Demodata\Provider\CronIntervalProvider;
use Shopware\Commercial\Subscription\Framework\Demodata\Provider\DateIntervalProvider;
use Shopware\Commercial\Subscription\Interval\IntervalCalculator;
use Shopware\Core\Checkout\Cart\CartCalculator;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\CronInterval;
use Shopware\Core\Framework\DataAbstractionLayer\FieldType\DateInterval;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('checkout')]
class SubscriptionGenerator implements DemodataGeneratorInterface
{
    /**
     * @var array<string, SalesChannelContext>
     */
    private array $contexts = [];

    /**
     * @param EntityRepository<SubscriptionCollection> $subscriptionRepository
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly CartCalculator $calculator,
        private readonly CartService $cartService,
        private readonly EntityRepository $subscriptionRepository,
        private readonly IntervalCalculator $intervalCalculator,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly AbstractSalesChannelContextFactory $contextFactory,
    ) {
    }

    public function getDefinition(): string
    {
        return SubscriptionDefinition::class;
    }

    /**
     * @param array<string, mixed> $options
     */
    public function generate(int $numberOfItems, DemodataContext $context, array $options = []): void
    {
        $context->getConsole()->progressStart($numberOfItems);

        $faker = $context->getFaker();
        $faker->addProvider(new CronIntervalProvider($faker));
        $faker->addProvider(new DateIntervalProvider($faker));

        $payload = [];

        $productIds = $this->getIds('product', 500);
        $customerIds = $this->getIds('customer', 100);
        $paymentMethodIds = $this->getIds('payment_method', 4);
        $shippingMethodsIds = $this->getIds('shipping_method', 2);
        $stateIds = $this->getStateMachineStateIds();
        $planIds = $this->getIds('subscription_plan', 100);
        $salesChannelIds = $this->getIds('sales_channel', 2);
        $tagIds = $this->getIds('tag', 100);
        $languageIds = $this->getIds('language', 10);
        $currencyIds = $this->getIds('currency', 10);

        for ($i = 0; $i < $numberOfItems; ++$i) {
            /** @var string $customerId */
            $customerId = $context->getFaker()->randomElement($customerIds);
            $salesChannelContext = $this->getContext($customerId, $salesChannelIds);

            $tags = $faker->randomElements(
                $tagIds,
                $faker->numberBetween(1, \min(3, \count($tagIds)))
            );

            $tags = \array_map(function ($id) {
                return ['id' => $id];
            }, $tags);

            $products = $faker->randomElements(
                $productIds,
                $faker->numberBetween(1, \min(3, \count($productIds)))
            );

            $productLineItems = \array_map(
                fn ($productId) => (new LineItem($productId, LineItem::PRODUCT_LINE_ITEM_TYPE, $productId, $faker->randomDigit() + 1))
                    ->setStackable(true)
                    ->setRemovable(true),
                $products
            );

            $productLineItems = new LineItemCollection($productLineItems);

            $cart = $this->cartService->createNew($salesChannelContext->getToken());
            $cart->addLineItems($productLineItems);
            $cart = $this->calculator->calculate($cart, $salesChannelContext);

            /** @var string $planId */
            $planId = $faker->randomElement($planIds);
            $subscriptionId = Uuid::randomHex();

            $customer = $this->getCustomerData($customerId);
            $addresses = $this->getRandomAddressData($customerId);

            $billingAddress = $addresses[0];
            $shippingAddress = \count($addresses) === 1 ? $addresses[0] : $addresses[1];

            $billingAddressPayload = $this->buildAddressPayload($billingAddress);
            $shippingAddressPayload = $this->buildAddressPayload($shippingAddress);

            $salutationId = isset($customer['salutation_id']) && \is_string($customer['salutation_id']) ? Uuid::fromBytesToHex($customer['salutation_id']) : null;

            /** @var string|null $vatIds */
            $vatIds = $customer['vat_ids'] ?? null;

            if ($vatIds) {
                $vatIds = \json_decode($vatIds, true);
            }

            $customerPayload = [
                'id' => Uuid::randomHex(),
                'subscriptionId' => $subscriptionId,
                'customerId' => $customerId,
                'salutationId' => $salutationId,
                'email' => $customer['email'],
                'firstName' => $customer['first_name'],
                'lastName' => $customer['last_name'],
                'company' => $customer['company'] ?? null,
                'title' => $customer['title'] ?? null,
                'customerNumber' => $customer['customer_number'] ?? null,
                'vatIds' => $vatIds,
            ];

            $payload[$i] = [
                'id' => $subscriptionId,
                'convertedOrder' => $cart->jsonSerialize(),
                'subscriptionNumber' => $this->numberRangeValueGenerator->getValue('subscription', $context->getContext(), null),
                'salesChannelId' => $faker->randomElement($salesChannelIds),
                'subscriptionPlanId' => $planId,
                'subscriptionPlanName' => 'Plan ' . $planId,
                'subscriptionCustomer' => $customerPayload,
                'billingAddress' => $billingAddressPayload,
                'shippingAddress' => $shippingAddressPayload,
                'paymentMethodId' => $faker->randomElement($paymentMethodIds),
                'shippingMethodId' => $faker->randomElement($shippingMethodsIds),
                'stateId' => $faker->randomElement($stateIds),
                'tags' => $tags,
                'languageId' => $faker->randomElement($languageIds),
                'currencyId' => $faker->randomElement($currencyIds),
                'itemRounding' => ['decimals' => 2, 'interval' => 0.01, 'roundForNet' => true],
                'totalRounding' => ['decimals' => 2, 'interval' => 0.01, 'roundForNet' => true],
            ];

            $intervalData = $this->getIntervalData($planId);
            $interval = new SubscriptionIntervalEntity();

            if (isset($intervalData)) {
                $payload[$i]['subscriptionIntervalId'] = Uuid::fromBytesToHex($intervalData['id']);

                $interval->setName($intervalData['name']);
                $interval->setDateInterval(new DateInterval($intervalData['date_interval']));
                $interval->setCronInterval(new CronInterval($intervalData['cron_interval']));
            } else {
                $payload[$i]['subscriptionIntervalId'] = null;

                $interval->setName('Interval for ' . $subscriptionId);

                /* @phpstan-ignore-next-line contextual issue */
                $interval->setDateInterval($faker->dateInterval());

                /* @phpstan-ignore-next-line contextual issue */
                $interval->setCronInterval($faker->cron());
            }

            $payload[$i]['subscriptionIntervalName'] = $interval->getName();
            $payload[$i]['dateInterval'] = $interval->getDateInterval();
            $payload[$i]['cronInterval'] = $interval->getCronInterval();
            $payload[$i]['nextSchedule'] = $this->intervalCalculator->getNextRunDate($interval);

            $context->getConsole()->progressAdvance();
        }

        $this->subscriptionRepository->upsert($payload, $context->getContext());

        $context->getConsole()->progressFinish();
    }

    /**
     * @throws Exception
     *
     * @return array<int, string>
     */
    private function getIds(string $table, int $count): array
    {
        /** @var string[] $result */
        $result = $this->connection->fetchFirstColumn('SELECT LOWER(HEX(id)) AS id FROM ' . $table . ' order by RAND() LIMIT ' . $count);

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function getStateMachineStateIds(): array
    {
        $sql = <<<SQL
SELECT LOWER(HEX(state_machine_state.id)) as id
FROM state_machine_state
LEFT JOIN `state_machine` ON `state_machine`.`id` = `state_machine_state`.`state_machine_id`
WHERE `state_machine`.`technical_name` = 'subscription.state';
SQL;

        /** @var string[] $result */
        $result = $this->connection->fetchFirstColumn($sql);

        return $result;
    }

    /**
     * @return array{id: string, name: string, cron_interval: string, date_interval: string}|null
     */
    private function getIntervalData(string $planId): ?array
    {
        $sql = <<<SQL
SELECT *
FROM subscription_interval
LEFT JOIN subscription_plan_interval_mapping ON subscription_interval.id = subscription_plan_interval_mapping.subscription_interval_id
LEFT JOIN subscription_interval_translation ON subscription_interval_translation.subscription_interval_id = subscription_interval.id
WHERE subscription_plan_interval_mapping.subscription_plan_id = :subscriptionPlanId
LIMIT 1;
SQL;

        /** @var array{id: string, name: string, cron_interval: string, date_interval: string}|false $data */
        $data = $this->connection->fetchAssociative($sql, ['subscriptionPlanId' => Uuid::fromHexToBytes($planId)]);

        if (!$data) {
            return null;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function getCustomerData(string $customerId): array
    {
        $sql = <<<SQL
SELECT *
FROM customer
WHERE customer.id = :customerId
LIMIT 1;
SQL;

        $data = $this->connection->fetchAssociative($sql, ['customerId' => Uuid::fromHexToBytes($customerId)]);

        if (!$data) {
            // @phpstan-ignore-next-line
            throw new Exception('Customer not found');
        }

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRandomAddressData(string $customerId): array
    {
        $sql = <<<SQL
SELECT *
FROM customer_address
WHERE customer_address.customer_id = :customerId
ORDER BY RAND()
LIMIT 2;
SQL;

        $data = $this->connection->fetchAllAssociative($sql, ['customerId' => Uuid::fromHexToBytes($customerId)]);

        if (!$data) {
            // @phpstan-ignore-next-line
            throw new Exception('Address not found');
        }

        return $data;
    }

    /**
     * @param array<string> $salesChannelIds
     */
    private function getContext(string $customerId, array $salesChannelIds): SalesChannelContext
    {
        if (isset($this->contexts[$customerId])) {
            return $this->contexts[$customerId];
        }

        $options = [
            SalesChannelContextService::CUSTOMER_ID => $customerId,
        ];

        $context = $this->contextFactory->create(Uuid::randomHex(), $salesChannelIds[\array_rand($salesChannelIds)], $options);

        return $this->contexts[$customerId] = $context;
    }

    /**
     * @param array<string, mixed> $address
     *
     * @return array<string, mixed>
     */
    private function buildAddressPayload(array $address): array
    {
        $countryId = (isset($address['country_id']) && \is_string($address['country_id'])) ? Uuid::fromBytesToHex($address['country_id']) : null;
        $countryStateId = (isset($address['country_state_id']) && \is_string($address['country_state_id'])) ? Uuid::fromBytesToHex($address['country_state_id']) : null;
        $salutationId = (isset($address['salutation_id']) && \is_string($address['salutation_id'])) ? Uuid::fromBytesToHex($address['salutation_id']) : null;

        return [
            'id' => Uuid::randomHex(),
            'countryId' => $countryId,
            'countryStateId' => $countryStateId,
            'salutationId' => $salutationId,
            'firstName' => $address['first_name'],
            'lastName' => $address['last_name'],
            'street' => $address['street'],
            'zipcode' => $address['zipcode'] ?? null,
            'city' => $address['city'],
            'company' => $address['company'] ?? null,
            'department' => $address['department'] ?? null,
            'title' => $address['title'] ?? null,
            'vatId' => $address['vat_id'] ?? null,
            'phoneNumber' => $address['phone_number'] ?? null,
            'additionalAddressLine1' => $address['additional_address_line_1'] ?? null,
            'additionalAddressLine2' => $address['additional_address_line_2'] ?? null,
        ];
    }
}

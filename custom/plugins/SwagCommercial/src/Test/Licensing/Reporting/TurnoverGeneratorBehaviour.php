<?php declare(strict_types=1);

namespace Shopware\Commercial\Test\Licensing\Reporting;

use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\IdsCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\Integration\Builder\Order\OrderBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
trait TurnoverGeneratorBehaviour
{
    private IdsCollection $stateMachineIds;

    /**
     * @before
     */
    public function initializeStateMachineIds(): void
    {
        $this->stateMachineIds = new IdsCollection();
    }

    abstract protected static function getContainer(): ContainerInterface;

    /**
     * @param array<int, array<string, array<int|string, mixed>|\DateTimeImmutable|string>> $orders
     *
     * @return array<int, string>
     */
    private function generateOrders(array $orders): array
    {
        $orderIds = [];

        foreach ($orders as $order) {
            $this->ensureOrderHasAtLeastOneLineItem($order);

            $orderIds[] = $this->createOrder($order);
        }

        return $orderIds;
    }

    /**
     * @param array<string, array<int|string, mixed>|\DateTimeImmutable|string> $orderData
     */
    private function createOrder(array $orderData): string
    {
        $orderNumber = Uuid::randomHex();
        $orderIds = new IdsCollection();

        $orderBuilder = new OrderBuilder($orderIds, $orderNumber);
        $orderBuilder->price(123.97)
            ->shippingCosts(0.0);

        foreach ($orderData as $property => $additionalData) {
            $orderBuilder->add($property, $additionalData);
        }

        $this->getContainer()->get('order.repository')
            ->create([$orderBuilder->build()], Context::createDefaultContext());

        return $orderIds->get($orderNumber);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function createLineItems(int $goods, int $nonGoods = 0): array
    {
        $lineItems = [];

        for ($i = 0; $i < $goods; ++$i) {
            $lineItems[] = [
                'identifier' => Uuid::randomHex(),
                'label' => Uuid::randomHex(),
                'quantity' => 1,
                'price' => [
                    'quantity' => 1,
                    'taxRules' => [],
                    'totalPrice' => 0.99,
                    'unitPrice' => 0.99,
                    'calculatedTaxes' => [],
                ],
                'good' => true,
            ];
        }

        for ($i = 0; $i < $nonGoods; ++$i) {
            $lineItems[] = [
                'identifier' => Uuid::randomHex(),
                'label' => Uuid::randomHex(),
                'quantity' => 1,
                'price' => [
                    'quantity' => 1,
                    'taxRules' => [],
                    'totalPrice' => 0.99,
                    'unitPrice' => 0.99,
                    'calculatedTaxes' => [],
                ],
                'good' => false,
            ];
        }

        return $lineItems;
    }

    private function createHistoryEntry(
        string $orderId,
        string $fromState,
        string $toState,
        \DateTimeImmutable $createdAt,
        string $versionId = Defaults::LIVE_VERSION
    ): void {
        $orderStateMachineId = $this->getOrderStateMachineId();
        $fromStateId = $this->getStateMachineStateId($fromState, $orderStateMachineId);
        $toStateId = $this->getStateMachineStateId($toState, $orderStateMachineId);

        $this->getContainer()->get(Connection::class)->executeStatement('
            INSERT INTO `state_machine_history` (`id`, `state_machine_id`, `entity_name`, `entity_id`, `from_state_id`, `to_state_id`, `action_name`, `created_at`)
            VALUES (:id, :smi, "order", :entity, :fromId, :toId, :action, :createdAt)
        ', [
            'id' => Uuid::randomBytes(),
            'smi' => $orderStateMachineId,
            'entity' => \json_encode(['id' => $orderId, 'version_id' => $versionId], \JSON_THROW_ON_ERROR),
            'fromId' => $fromStateId,
            'toId' => $toStateId,
            'action' => $toState,
            'createdAt' => $createdAt->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function getOrderStateMachineId(): string
    {
        if ($this->stateMachineIds->has(OrderStates::STATE_MACHINE)) {
            return $this->stateMachineIds->get(OrderStates::STATE_MACHINE);
        }

        /** @var string|false $orderStateMachineId */
        $orderStateMachineId = $this->getContainer()->get(Connection::class)->executeQuery('
            SELECT `id`
            FROM `state_machine`
            WHERE `technical_name` = :technicalName
        ', ['technicalName' => OrderStates::STATE_MACHINE])->fetchOne();

        if ($orderStateMachineId === false) {
            throw new \RuntimeException('could not fetch order.state id');
        }

        $this->stateMachineIds->set(OrderStates::STATE_MACHINE, $orderStateMachineId);

        return $orderStateMachineId;
    }

    private function getStateMachineStateId(string $technicalName, string $orderStateMachineId): string
    {
        if ($this->stateMachineIds->has($technicalName)) {
            return $this->stateMachineIds->get($technicalName);
        }

        /** @var string|false $orderStateId */
        $orderStateId = $this->getContainer()->get(Connection::class)->executeQuery('
            SELECT `id`
            FROM `state_machine_state`
            WHERE `technical_name` = :technicalName AND `state_machine_id` = :stateMachineId
        ', ['technicalName' => $technicalName, 'stateMachineId' => $orderStateMachineId])->fetchOne();

        if ($orderStateId === false) {
            throw new \RuntimeException("could not fetch id for state {$technicalName}");
        }

        $this->stateMachineIds->set($technicalName, $orderStateId);

        return $orderStateId;
    }

    /**
     * @param array<string, mixed> $actual
     */
    private function expectValueInArray(string $arrayKey, mixed $expected, array $actual): void
    {
        $keys = explode('.', $arrayKey);

        foreach ($keys as $key) {
            static::assertIsArray($actual);
            static::assertArrayHasKey($key, $actual);

            $actual = $actual[$key];
        }

        static::assertEquals($expected, $actual);
    }

    /**
     * @param array<string, mixed> $order
     */
    private function ensureOrderHasAtLeastOneLineItem(array &$order): void
    {
        if (!isset($order['lineItems'])) {
            $order['lineItems'] = $this->createLineItems(1);
        }
    }
}

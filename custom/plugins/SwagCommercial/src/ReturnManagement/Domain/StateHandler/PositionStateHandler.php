<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\StateHandler;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Domain\Returning\OrderReturnException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\StateMachineException;

/**
 * This class is for purpose transit the line items and return line items by updated directly
 * into DB without StateMachineRegistry to optimize the performances for positions state.
 */
#[Package('checkout')]
class PositionStateHandler
{
    final public const STATE_MACHINE = 'order_line_item.state';
    final public const STATE_OPEN = 'open';
    final public const STATE_SHIPPED = 'shipped';
    final public const STATE_SHIPPED_PARTIALLY = 'shipped_partially';
    final public const STATE_RETURN_REQUESTED = 'return_requested';
    final public const STATE_RETURNED = 'returned';
    final public const STATE_RETURNED_PARTIALLY = 'returned_partially';
    final public const STATE_CANCELLED = 'cancelled';

    final public const RETURN_ITEM_STATES = [
        self::STATE_RETURNED,
        self::STATE_RETURNED_PARTIALLY,
        self::STATE_RETURN_REQUESTED,
    ];

    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    /**
     * @var array<string, string>
     */
    private array $states;

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityRepository $lineItemRepository,
        private readonly EntityRepository $returnLineItemRepository
    ) {
    }

    /**
     * @param array<string> $lineItemIds
     *
     * @return array<string>
     */
    public function transitOrderLineItems(array $lineItemIds, string $toPlace, Context $context): array
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        if (\count($lineItemIds) === 0) {
            return [];
        }

        $omittedIds = [];
        // TODO: transition rules like a standard State Machine
        if (\in_array($toPlace, [static::STATE_RETURNED, static::STATE_RETURNED_PARTIALLY], true)) {
            $criteria = new Criteria($lineItemIds);
            $criteria->addFilter(new EqualsFilter('state.technicalName', static::STATE_CANCELLED));
            $omittedIds = $this->lineItemRepository->searchIds($criteria, $context)->getIds();
        }

        $toPlaceId = $this->getStateId($toPlace);

        $payload = [];
        $lineItemIds = array_diff($lineItemIds, $omittedIds);
        foreach ($lineItemIds as $itemId) {
            $payload[] = [
                'id' => $itemId,
                'stateId' => $toPlaceId,
            ];
        }
        $this->lineItemRepository->upsert($payload, $context);

        return $lineItemIds;
    }

    /**
     * @param string[] $orderReturnLineItemIds
     *
     * @return string[]
     */
    public function transitReturnItems(array $orderReturnLineItemIds, string $toPlace, Context $context): array
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        if (\count($orderReturnLineItemIds) === 0) {
            return [];
        }

        if (!\in_array($toPlace, static::RETURN_ITEM_STATES, true)) {
            throw OrderReturnException::invalidReturnItemStates();
        }

        $toPlaceId = $this->getStateId($toPlace);

        $payload = [];
        foreach ($orderReturnLineItemIds as $itemId) {
            $payload[] = [
                'id' => $itemId,
                'stateId' => $toPlaceId,
            ];
        }
        $this->returnLineItemRepository->upsert($payload, $context);

        $this->transitAutoOrderLineItemStates($orderReturnLineItemIds, $toPlaceId, $context);

        return $orderReturnLineItemIds;
    }

    /**
     * @internal
     */
    public function getStateId(string $toPlace): string
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        $this->states ??= $this->loadStates();

        if (!isset($this->states[$toPlace])) {
            throw StateMachineException::stateMachineStateNotFound(self::STATE_MACHINE, $toPlace);
        }

        return $this->states[$toPlace];
    }

    /**
     * @return array<string, string>
     */
    private function loadStates(): array
    {
        /** @var array<string, string> $result */
        $result = $this->connection->fetchAllKeyValue(
            'SELECT `state_machine_state`.`technical_name`, LOWER(HEX(`state_machine_state`.`id`)) `state_id`
                FROM `state_machine_state` JOIN `state_machine`
                    ON `state_machine_state`.`state_machine_id` = `state_machine`.`id`
                WHERE
                    `state_machine`.`technical_name` = :machine_technical_name',
            [
                'machine_technical_name' => self::STATE_MACHINE,
            ],
        );

        return $result;
    }

    /**
     * Automation transition when Return Item change state
     *
     * @param string[] $orderReturnLineItemIds
     */
    private function transitAutoOrderLineItemStates(array $orderReturnLineItemIds, string $toPlaceId, Context $context): void
    {
        if (!isset($this->states[self::STATE_RETURNED])) {
            throw StateMachineException::stateMachineStateNotFound(self::STATE_MACHINE, self::STATE_RETURNED);
        }

        if (!isset($this->states[self::STATE_RETURNED_PARTIALLY])) {
            throw StateMachineException::stateMachineStateNotFound(self::STATE_MACHINE, self::STATE_RETURNED_PARTIALLY);
        }

        $sql = 'SELECT LOWER(HEX(`order_line_item`.id)) `id`,
                CASE
                    WHEN :check_returned AND `order_line_item`.`quantity` > `order_return_line_item`.`quantity`
                        THEN :returned_partially
                        ELSE :target_state_id
                END `stateId`
                FROM `order_line_item` JOIN
                    (
                        SELECT `order_line_item_id`, `order_line_item_version_id`, SUM(`quantity`) quantity
                        FROM `order_return_line_item`
                           WHERE `id` IN (:order_return_line_item_ids) AND `version_id` = :version_id
                        GROUP BY `order_line_item_id`, `order_line_item_version_id`
                    ) `order_return_line_item`
                    ON `order_line_item`.`id` = `order_return_line_item`.`order_line_item_id` AND `order_line_item`.`version_id` = `order_return_line_item`.`order_line_item_version_id`';

        $result = $this->connection->fetchAllAssociative(
            $sql,
            [
                'order_return_line_item_ids' => Uuid::fromHexToBytesList($orderReturnLineItemIds),
                'check_returned' => $toPlaceId === $this->states[self::STATE_RETURNED],
                'returned_partially' => $this->states[self::STATE_RETURNED_PARTIALLY],
                'target_state_id' => $toPlaceId,
                'version_id' => Uuid::fromHexToBytes($context->getVersionId()),
                'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ],
            [
                'order_return_line_item_ids' => ArrayParameterType::STRING,
            ]
        );

        $this->lineItemRepository->update($result, $context);
    }
}

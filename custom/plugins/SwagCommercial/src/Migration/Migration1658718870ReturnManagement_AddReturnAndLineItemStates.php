<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnStates;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\StateMachineMigration;
use Shopware\Core\Migration\Traits\StateMachineMigrationTrait;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1658718870ReturnManagement_AddReturnAndLineItemStates extends MigrationStep
{
    use ImportTranslationsTrait;
    use StateMachineMigrationTrait;

    public function getCreationTimestamp(): int
    {
        return 1658718870;
    }

    public function update(Connection $connection): void
    {
        $this->createOrderReturnStates($connection);
        $this->createLineItemStates($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // TODO: Implement updateDestructive() method.
    }

    private function createOrderReturnStates(Connection $connection): void
    {
        $stateMachine = new StateMachineMigration(
            OrderReturnStates::STATE_MACHINE,
            'Status Retoure',
            'Return state',
            [
                StateMachineMigration::state(OrderReturnStates::STATE_OPEN, 'Offen', 'Open'),
                StateMachineMigration::state(OrderReturnStates::STATE_IN_PROGRESS, 'In Bearbeitung', 'In Progress'),
                StateMachineMigration::state(OrderReturnStates::STATE_DONE, 'Abgeschlossen', 'Done'),
                StateMachineMigration::state(OrderReturnStates::STATE_CANCELLED, 'Abgebrochen', 'Cancelled'),
            ],
            [
                // open -> in-progress
                StateMachineMigration::transition(
                    StateMachineTransitionActions::ACTION_PROCESS,
                    OrderReturnStates::STATE_OPEN,
                    OrderReturnStates::STATE_IN_PROGRESS
                ),
                // in-progress -> done
                StateMachineMigration::transition(
                    StateMachineTransitionActions::ACTION_COMPLETE,
                    OrderReturnStates::STATE_IN_PROGRESS,
                    OrderReturnStates::STATE_DONE
                ),
                // done -> open
                StateMachineMigration::transition(
                    StateMachineTransitionActions::ACTION_REOPEN,
                    OrderReturnStates::STATE_DONE,
                    OrderReturnStates::STATE_OPEN
                ),
                // open -> cancelled
                StateMachineMigration::transition(
                    StateMachineTransitionActions::ACTION_CANCEL,
                    OrderReturnStates::STATE_OPEN,
                    OrderReturnStates::STATE_CANCELLED
                ),
                // in-progress -> cancelled
                StateMachineMigration::transition(
                    StateMachineTransitionActions::ACTION_CANCEL,
                    OrderReturnStates::STATE_IN_PROGRESS,
                    OrderReturnStates::STATE_CANCELLED
                ),
                // cancelled -> open
                StateMachineMigration::transition(
                    StateMachineTransitionActions::ACTION_REOPEN,
                    OrderReturnStates::STATE_CANCELLED,
                    OrderReturnStates::STATE_OPEN
                ),
            ],
            OrderReturnStates::STATE_OPEN
        );

        $this->import($stateMachine, $connection);
    }

    private function createLineItemStates(Connection $connection): void
    {
        $stateMachine = new StateMachineMigration(
            PositionStateHandler::STATE_MACHINE,
            'Status Position',
            'Position state',
            [
                StateMachineMigration::state(PositionStateHandler::STATE_OPEN, 'Offen', 'Open'),
                StateMachineMigration::state(PositionStateHandler::STATE_SHIPPED, 'Versandt', 'Shipped'),
                StateMachineMigration::state(PositionStateHandler::STATE_SHIPPED_PARTIALLY, 'Teilweise versandt', 'Shipped partially'),
                StateMachineMigration::state(PositionStateHandler::STATE_RETURN_REQUESTED, 'Retour angefragt', 'Return requested'),
                StateMachineMigration::state(PositionStateHandler::STATE_RETURNED, 'Retour', 'Returned'),
                StateMachineMigration::state(PositionStateHandler::STATE_RETURNED_PARTIALLY, 'Teilretour', 'Returned partially'),
                StateMachineMigration::state(PositionStateHandler::STATE_CANCELLED, 'Abgebrochen', 'Cancelled'),
            ],
            [],
            PositionStateHandler::STATE_OPEN
        );

        $this->import($stateMachine, $connection);
    }
}

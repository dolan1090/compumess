<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\DelayedFlowAction\Domain\Handler;

use Shopware\Commercial\FlowBuilder\DelayedFlowAction\Entity\DelayActionCollection;
use Shopware\Commercial\FlowBuilder\DelayedFlowAction\Entity\DelayActionEntity;
use Shopware\Core\Content\Flow\Dispatching\AbstractFlowLoader;
use Shopware\Core\Content\Flow\Dispatching\FlowExecutor;
use Shopware\Core\Content\Flow\Dispatching\FlowFactory;
use Shopware\Core\Content\Flow\Dispatching\Struct\ActionSequence;
use Shopware\Core\Content\Flow\Dispatching\Struct\Flow;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Event\CustomerAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\OrderAware;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextServiceParameters;

/**
 * @internal
 */
#[Package('business-ops')]
class DelayActionHandler
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractFlowLoader $flowLoader,
        private readonly SalesChannelContextServiceInterface $salesChannelContextService,
        private readonly SalesChannelContextRestorer $channelContextRestorer,
        private readonly FlowFactory $flowFactory,
        private readonly FlowExecutor $flowExecutor,
        private readonly EntityRepository $delayRepository
    ) {
    }

    /**
     * @param array<int, string> $delayIds
     */
    public function handle(array $delayIds): void
    {
        if (empty($delayIds)) {
            return;
        }

        $delays = $this->delayRepository->search(
            new Criteria($delayIds),
            Context::createDefaultContext()
        );

        if (!$delays->getTotal()) {
            return;
        }

        $flowsEventName = $this->flowLoader->load();

        /** @var DelayActionEntity $delay */
        foreach ($delays->getElements() as $delay) {
            $stored = $delay->getStored();
            $event = $this->flowFactory->restore($delay->getEventName(), $this->restoreContext($stored), $stored);
            $delaySequenceId = $delay->getDelaySequenceId();

            $flows = $flowsEventName[$event->getName()] ?? [];
            if (empty($flows)) {
                return;
            }

            $index = \array_search($delay->getFlowId(), \array_column($flows, 'id'), true);
            if ($index === false) {
                return;
            }

            /** @var Flow $flow */
            $flow = $flows[$index]['payload'];
            $flat = $flow->getFlat();

            /** @var ActionSequence|null $sequence */
            $sequence = $flat[$delaySequenceId] ?? null;
            if (!$sequence) {
                return;
            }

            $nextSequence = $sequence->nextAction;
            if (!$nextSequence) {
                return;
            }

            $flow->jump($nextSequence->sequenceId);
            $this->flowExecutor->execute($flow, $event);
        }

        /** @var DelayActionCollection $entities */
        $entities = $delays->getEntities();
        $this->deleteDelayAction($entities);
    }

    /**
     * @param array<string, mixed> $stored
     */
    private function restoreContext(array $stored): Context
    {
        $context = Context::createDefaultContext();

        if (isset($stored[OrderAware::ORDER_ID])) {
            /** @var string $orderId */
            $orderId = $stored[OrderAware::ORDER_ID];

            return $this->channelContextRestorer->restoreByOrder($orderId, $context)->getContext();
        }

        if (isset($stored[CustomerAware::CUSTOMER_ID])) {
            /** @var string $customerId */
            $customerId = $stored[CustomerAware::CUSTOMER_ID];

            return $this->channelContextRestorer->restoreByCustomer($customerId, $context)->getContext();
        }

        /** @var string $salesChannelId */
        $salesChannelId = $stored[MailAware::SALES_CHANNEL_ID] ?? Defaults::SALES_CHANNEL_TYPE_API;

        return $this->salesChannelContextService->get(
            new SalesChannelContextServiceParameters(
                $salesChannelId,
                Uuid::randomHex(),
                $context->getLanguageId(),
                $context->getCurrencyId(),
                null,
                $context,
                null,
            )
        )->getContext();
    }

    private function deleteDelayAction(DelayActionCollection $delays): void
    {
        /** @var array<string> $ids */
        $ids = $delays->getIds();

        if (!empty($ids)) {
            $ids = \array_map(static fn (string $id): array => ['id' => $id], \array_values($ids));

            $this->delayRepository->delete($ids, Context::createDefaultContext());
        }
    }
}

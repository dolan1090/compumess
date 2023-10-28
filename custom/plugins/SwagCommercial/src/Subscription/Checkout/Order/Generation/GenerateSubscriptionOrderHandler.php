<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Order\Generation;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: GenerateSubscriptionOrder::class)]
#[Package('checkout')]
final class GenerateSubscriptionOrderHandler
{
    public function __construct(
        private readonly GenerateSubscriptionOrderService $generateSubscriptionOrderService,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(GenerateSubscriptionOrder $message): void
    {
        try {
            $this->generateSubscriptionOrderService->generateOrderFromSubscription(
                $message->getSubscriptionId(),
                Context::createDefaultContext()
            );
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}

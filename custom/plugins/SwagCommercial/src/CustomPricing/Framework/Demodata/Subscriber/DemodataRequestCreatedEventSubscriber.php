<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Framework\Demodata\Subscriber;

use Shopware\Commercial\CustomPricing\Entity\CustomPrice\CustomPriceDefinition;
use Shopware\Core\Framework\Demodata\Event\DemodataRequestCreatedEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class DemodataRequestCreatedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            DemodataRequestCreatedEvent::class => 'demodataRequestCreated',
        ];
    }

    public function demodataRequestCreated(DemodataRequestCreatedEvent $event): void
    {
        $input = $event->getInput();

        $count = $input->getOption('custom-prices');
        if (\is_string($count) && (int) $count > 0) {
            $event->getRequest()->add(CustomPriceDefinition::class, (int) $count);
        }
    }
}

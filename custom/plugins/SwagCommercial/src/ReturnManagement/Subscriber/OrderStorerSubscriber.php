<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Subscriber;

use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class OrderStorerSubscriber implements EventSubscriberInterface
{
    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    public static function getSubscribedEvents(): array
    {
        return [
            'flow.storer.order.criteria.event' => 'handleCriteria',
        ];
    }

    public function handleCriteria(BeforeLoadStorableFlowDataEvent $event): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            return;
        }

        $criteria = $event->getCriteria();

        $criteria->addAssociation('returns.state')
            ->addAssociation('returns.lineItems.lineItem');
    }
}

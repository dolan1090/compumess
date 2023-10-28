<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Event;

use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionDefinition;
use Shopware\Commercial\Subscription\Entity\Subscription\SubscriptionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class CheckoutSubscriptionPlacedEvent extends Event implements SalesChannelAware, MailAware, SubscriptionAware, FlowEventAware
{
    final public const EVENT_NAME = 'checkout.subscription.placed';

    public function __construct(
        private readonly SalesChannelContext $salesChannelContext,
        private readonly SubscriptionEntity $subscription,
        private ?MailRecipientStruct $mailRecipientStruct = null,
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelContext->getSalesChannelId();
    }

    public function getSubscription(): SubscriptionEntity
    {
        return $this->subscription;
    }

    public function getSubscriptionId(): string
    {
        return $this->subscription->getId();
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $email = $this->subscription->getSubscriptionCustomer()?->getEmail();
            $name = $this->subscription->getSubscriptionCustomer()?->getFirstName() . ' ' . $this->subscription->getSubscriptionCustomer()?->getLastName();

            $this->mailRecipientStruct = new MailRecipientStruct([
                $email => $name,
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('subscription', new EntityType(SubscriptionDefinition::class));
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Event;

use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnDefinition;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Core\Checkout\Order\OrderException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\OrderAware;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @final
 *
 * @codeCoverageIgnore
 */
#[Package('checkout')]
class OrderReturnCreatedEvent extends Event implements ShopwareSalesChannelEvent, OrderAware, MailAware, FlowEventAware
{
    final public const EVENT_NAME = 'checkout.order.return.created';

    public function __construct(
        private readonly OrderReturnEntity $orderReturn,
        private readonly SalesChannelContext $context,
        private ?MailRecipientStruct $mailRecipientStruct = null
    ) {
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('return', new EntityType(OrderReturnDefinition::class));
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if ($this->mailRecipientStruct instanceof MailRecipientStruct) {
            return $this->mailRecipientStruct;
        }

        $order = $this->orderReturn->getOrder();
        if ($order === null) {
            throw OrderException::orderNotFound($this->orderReturn->getOrderId());
        }

        if ($order->getOrderCustomer() === null) {
            throw OrderException::missingAssociation('orderCustomer');
        }

        $this->mailRecipientStruct = new MailRecipientStruct([
            $order->getOrderCustomer()->getEmail() => $order->getOrderCustomer()->getFirstName() . ' ' . $order->getOrderCustomer()->getLastName(),
        ]);

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->context->getSalesChannelId();
    }

    public function getOrderId(): string
    {
        return $this->orderReturn->getOrderId();
    }
}

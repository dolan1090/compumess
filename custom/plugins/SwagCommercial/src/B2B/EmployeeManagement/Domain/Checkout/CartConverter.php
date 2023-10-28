<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Checkout;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CartConverter implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => 'onCartConvertedEvent',
        ];
    }

    public function onCartConvertedEvent(CartConvertedEvent $event): void
    {
        $customer = $event->getSalesChannelContext()->getCustomer();
        if (!$customer) {
            return;
        }

        $employee = $customer->getExtension('b2bEmployee');
        if (!$employee instanceof EmployeeEntity) {
            return;
        }

        $convertedCart = $event->getConvertedCart();

        $convertedCart['orderEmployee'] = [[
            'employeeId' => $employee->getId(),
            'firstName' => $employee->getFirstName(),
            'lastName' => $employee->getLastName(),
        ]];

        $event->setConvertedCart($convertedCart);
    }
}

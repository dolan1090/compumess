<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework;

use Shopware\Core\Framework\Event\ShopwareEvent;
use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('core')]
class SubscriptionFrameworkException extends HttpException
{
    public const INVALID_EVENT_CLASS = 'FRAMEWORK__INVALID_EVENT_CLASS';

    public static function invalidEventClass(string $class): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::INVALID_EVENT_CLASS,
            'Event {{ class }} must be an instance of {{ expectedClass }} or {{ shopwareExpectedClass }}.',
            ['class' => $class, 'expectedClass' => Event::class, 'shopwareExpectedClass' => ShopwareEvent::class]
        );
    }
}

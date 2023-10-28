<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Event;

use Shopware\Core\Checkout\Document\Event\DocumentOrderEvent;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
final class PartialCancellationOrdersEvent extends DocumentOrderEvent
{
}

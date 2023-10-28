<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class GuestCustomerError extends Error
{
    private const KEY = 'guest-customer-not-allowed';

    public function __construct()
    {
        $this->message = 'Guest customers are not allowed to purchase subscriptions.';

        parent::__construct($this->message);
    }

    public function getParameters(): array
    {
        return [];
    }

    public function getId(): string
    {
        return self::KEY;
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getLevel(): int
    {
        return self::LEVEL_ERROR;
    }

    public function blockOrder(): bool
    {
        return true;
    }

    public function isPersistent(): bool
    {
        return false;
    }
}

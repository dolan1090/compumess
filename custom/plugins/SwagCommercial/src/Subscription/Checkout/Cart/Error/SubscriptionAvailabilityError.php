<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionAvailabilityError extends Error
{
    public const PLAN = 'plan';
    public const INTERVAL = 'interval';

    private const KEY = 'subscription-%s-blocked';

    public function __construct(
        private string $entity,
        private string $id
    ) {
        $this->message = sprintf(
            'Subscription %s with id %s is not available.',
            $entity,
            $id
        );

        parent::__construct($this->message);
    }

    public function getParameters(): array
    {
        return [
            'entity' => \sprintf('subscription_%s', $this->entity),
            'id' => $this->id,
        ];
    }

    public function getId(): string
    {
        return \sprintf(self::KEY, $this->entity);
    }

    public function getMessageKey(): string
    {
        return \sprintf(self::KEY, $this->entity);
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

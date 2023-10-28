<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Framework\Log\Package;

#[Package('checkout')]
class SubscriptionProductMappingError extends Error
{
    private const KEY = 'subscription-product-mapping-missing';

    public function __construct(
        private string $productId,
        private string $planId,
    ) {
        $this->message = sprintf(
            'Product %s is not available with subscription plan %s and has been removed.',
            $productId,
            $planId,
        );

        parent::__construct($this->message);
    }

    public function getParameters(): array
    {
        return [
            'productId' => $this->productId,
            'planId' => $this->planId,
        ];
    }

    public function getId(): string
    {
        return \sprintf('%s-%s', self::KEY, $this->productId);
    }

    public function getMessageKey(): string
    {
        return self::KEY;
    }

    public function getLevel(): int
    {
        return self::LEVEL_WARNING;
    }

    public function blockOrder(): bool
    {
        return false;
    }

    public function isPersistent(): bool
    {
        return true;
    }
}

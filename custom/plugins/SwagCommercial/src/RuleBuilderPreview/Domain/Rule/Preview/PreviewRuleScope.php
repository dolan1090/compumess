<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\RuleBuilderPreview\Domain\Rule\Preview;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Rule\CartRuleScope;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * @internal
 */
#[Package('business-ops')]
final class PreviewRuleScope extends CartRuleScope
{
    public function __construct(
        Cart $cart,
        SalesChannelContext $context,
        private readonly ?\DateTimeImmutable $previewDateTime = null
    ) {
        parent::__construct($cart, $context);
    }

    public function getCurrentTime(): \DateTimeImmutable
    {
        if ($this->previewDateTime) {
            return $this->previewDateTime;
        }

        return new \DateTimeImmutable();
    }
}

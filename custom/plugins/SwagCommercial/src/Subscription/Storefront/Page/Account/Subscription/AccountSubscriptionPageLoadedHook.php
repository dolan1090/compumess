<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront\Page\Account\Subscription;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Script\Execution\Awareness\SalesChannelContextAwareTrait;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\PageLoadedHook;

/**
 * Triggered when the AccountSubscriptionPage is loaded
 *
 * @hook-use-case data_loading
 *
 * @since 6.5.5.0
 *
 * @final
 */
#[Package('checkout')]
class AccountSubscriptionPageLoadedHook extends PageLoadedHook
{
    use SalesChannelContextAwareTrait;

    final public const HOOK_NAME = 'account-subscription-page-loaded';

    public function __construct(
        private readonly AccountSubscriptionPage $page,
        SalesChannelContext $context
    ) {
        parent::__construct($context->getContext());
        $this->salesChannelContext = $context;
    }

    public function getName(): string
    {
        return self::HOOK_NAME;
    }

    public function getPage(): AccountSubscriptionPage
    {
        return $this->page;
    }
}

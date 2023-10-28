<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Storefront\Framework\Twig\Extension;

use Shopware\Commercial\B2B\QuickOrder\Domain\CustomerSpecificFeature\CustomerSpecificFeatureService;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSpecificFeatureTwigExtension extends AbstractExtension
{
    public function __construct(private readonly CustomerSpecificFeatureService $customerSpecificFeatureService)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('customerHasFeature', $this->isAllowed(...), ['needs_context' => true]),
        ];
    }

    /**
     * @param array<string, mixed> $twigContext
     */
    public function isAllowed(array $twigContext, string $feature): bool
    {
        $customerId = null;
        if (\array_key_exists('context', $twigContext) && $twigContext['context'] instanceof SalesChannelContext) {
            $customerId = $twigContext['context']->getCustomerId();
        }

        return $this->customerSpecificFeatureService->isAllowed($customerId, $feature);
    }
}

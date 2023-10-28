<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Storefront;

use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Routing\SubscriptionRequest;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

#[Package('checkout')]
class TemplateDataExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return [];
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request) {
            return [];
        }

        /** @var SalesChannelContext|null $context */
        $context = $request->attributes->get(SubscriptionRequest::ATTRIBUTE_SUBSCRIPTION_SALES_CHANNEL_CONTEXT_OBJECT);

        if (!$context) {
            return [];
        }

        $data = [
            'context' => $context,
        ];

        if ($request->attributes->has('_controllerName')) {
            $data['controllerName'] = $request->attributes->get('_controllerName');
        }
        if ($request->attributes->has('_controllerAction')) {
            $data['controllerAction'] = $request->attributes->get('_controllerAction');
        }

        return $data;
    }
}

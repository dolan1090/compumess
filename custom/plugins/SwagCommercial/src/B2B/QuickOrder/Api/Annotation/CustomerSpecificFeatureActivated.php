<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Api\Annotation;

use Shopware\Commercial\B2B\QuickOrder\Domain\CustomerSpecificFeature\CustomerSpecificFeatureService;
use Shopware\Commercial\B2B\QuickOrder\Exception\CustomerSpecificFeatureException;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\KernelListenerPriorities;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSpecificFeatureActivated implements EventSubscriberInterface
{
    public const ATTRIBUTE_FEATURE_CODE = '_b2bFeatureCode';

    public function __construct(
        private readonly CustomerSpecificFeatureService $customerSpecificFeatureService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => [
                ['validate', KernelListenerPriorities::KERNEL_CONTROLLER_EVENT_SCOPE_VALIDATE],
            ],
        ];
    }

    public function validate(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        /** @var string|null $featureCode */
        $featureCode = $request->attributes->get(self::ATTRIBUTE_FEATURE_CODE);

        if (empty($featureCode)) {
            return;
        }

        $context = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        $customer = $context instanceof SalesChannelContext ? $context->getCustomer() : null;

        if (!$customer) {
            throw CartException::customerNotLoggedIn();
        }

        if ($this->customerSpecificFeatureService->isAllowed($customer->getId(), $featureCode)) {
            return;
        }

        throw CustomerSpecificFeatureException::notAllowed($featureCode);
    }
}

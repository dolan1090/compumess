<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration;

use Shopware\Commercial\B2B\B2bComponents;
use Shopware\Core\Checkout\Customer\Event\CustomerGroupRegistrationAccepted;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerGroupRegistrationSubscriber implements EventSubscriberInterface
{
    final public const ACTIVATE_FEATURE_EMPLOYEE_MANAGEMENT_FIELD = 'employee_management_activate_feature';
    final public const ACTIVATE_FEATURE_QUICK_ORDER_FIELD = 'swag_quick_order_activate_feature';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $businessPartnerRepository,
        private readonly EntityRepository $customerSpecificFeatureRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerGroupRegistrationAccepted::class => 'onCustomerGroupRegistrationAccepted',
        ];
    }

    public function onCustomerGroupRegistrationAccepted(CustomerGroupRegistrationAccepted $event): void
    {
        $features = [];
        $customerGroup = $event->getCustomerGroup();

        $b2bActive = (bool) $customerGroup->getCustomFieldsValue(self::ACTIVATE_FEATURE_EMPLOYEE_MANAGEMENT_FIELD);
        if ($b2bActive) {
            $this->createB2bBusinessPartner($event);
            $features[B2bComponents::EMPLOYEE_MANAGEMENT->value] = true;
        }

        $quickOrderActive = (bool) $customerGroup->getCustomFieldsValue(self::ACTIVATE_FEATURE_QUICK_ORDER_FIELD);
        if ($quickOrderActive) {
            $features[B2bComponents::QUICK_ORDER->value] = true;
        }

        if (empty($features)) {
            return;
        }

        $this->customerSpecificFeatureRepository->upsert([
            [
                'customerId' => $event->getCustomerId(),
                'features' => $features,
            ],
        ], $event->getContext());
    }

    public function createB2bBusinessPartner(CustomerGroupRegistrationAccepted $event): void
    {
        $this->businessPartnerRepository->upsert([[
            'id' => $event->getCustomer()->getId(),
            'businessPartnerCustomerId' => $event->getCustomer()->getId(),
        ]], $event->getContext());
    }
}

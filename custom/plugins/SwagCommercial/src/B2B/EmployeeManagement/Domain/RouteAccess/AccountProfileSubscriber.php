<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\RouteAccess;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\BusinessPartner\BusinessPartnerEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Role\RoleCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Account\Profile\AccountProfilePageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class AccountProfileSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $roleRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AccountProfilePageLoadedEvent::class => 'onProfileLoaded',
        ];
    }

    public function onProfileLoaded(AccountProfilePageLoadedEvent $event): void
    {
        $customer = $event->getSalesChannelContext()->getCustomer();

        if (!$customer) {
            return;
        }

        /** @var ?EmployeeEntity $employee */
        $employee = $customer->getExtension('b2bEmployee');

        /** @var ?BusinessPartnerEntity $businessPartner */
        $businessPartner = $customer->getExtension('b2bBusinessPartner');

        if ($employee === null || $businessPartner === null) {
            return;
        }

        $roles = $this->getAvailableRoles($event->getSalesChannelContext(), $businessPartner);

        $page = $event->getPage();
        $page->addExtension('b2bEmployee', $employee);
        $page->addExtension('b2bAvailableRoles', $roles);
    }

    private function getAvailableRoles(SalesChannelContext $salesChannelContext, BusinessPartnerEntity $businessPartner): RoleCollection
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('businessPartnerCustomerId', $businessPartner->getCustomerId()));

        /** @var RoleCollection $roles */
        $roles = $this->roleRepository->search($criteria, $salesChannelContext->getContext())
            ->getEntities();

        return $roles;
    }
}

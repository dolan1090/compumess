<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration;

use GuzzleHttp\Psr7\Uri;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Util\Random;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[Package('checkout')]
class EmployeeInviteService
{
    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EntityRepository $employeeRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SystemConfigService $systemConfigService
    ) {
    }

    public function invite(EmployeeEntity $employee, Context $context): void
    {
        $criteria = (new Criteria([$employee->getBusinessPartnerCustomerId()]))
            ->addAssociation('salesChannel.domains');

        /** @var CustomerEntity|null $businessPartner */
        $businessPartner = $this->customerRepository->search($criteria, $context)->first();

        if (!$businessPartner?->getSalesChannel()) {
            throw EmployeeManagementException::businessPartnerNotFound($employee->getBusinessPartnerCustomerId());
        }

        $salesChannel = $businessPartner->getSalesChannel();

        $hash = Random::getAlphanumericString(32);
        $now = new \DateTimeImmutable();

        $data = [
            'id' => $employee->getId(),
            'recoveryHash' => $hash,
            'recoveryTime' => $now->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $this->employeeRepository->update([$data], $context);

        $recoverUrl = $this->getInviteUrl($hash, $salesChannel, $context);
        $inviteEvent = new EmployeeAccountInviteEvent($context, $employee, $businessPartner, $salesChannel, $recoverUrl);
        $this->eventDispatcher->dispatch($inviteEvent);
    }

    private function getInviteUrl(
        string $hash,
        SalesChannelEntity $salesChannel,
        Context $context
    ): string {
        $urlTemplate = $this->systemConfigService->get(
            'b2b.employee.invitationURL',
            $salesChannel->getId()
        );
        $urlTemplate = \is_string($urlTemplate) ? $urlTemplate : '/account/business-partner/employee/invite/%%RECOVERHASH%%';

        $domains = $salesChannel->getDomains();
        if (!$domains) {
            throw EmployeeManagementException::invalidRequestArgument('Sales channel without domains provided.');
        }

        $domain = $domains->first();
        if (!$domain) {
            throw EmployeeManagementException::invalidRequestArgument('Sales channel without domains provided.');
        }

        foreach ($domains as $salesChannelDomain) {
            /** todo NEXT-30163 - Change to employee language */
            if ($salesChannelDomain->getLanguageId() === $context->getLanguageId()) {
                $domain = $salesChannelDomain;

                break;
            }
        }

        $url = rtrim($domain->getUrl(), '/') . str_replace(
            '%%RECOVERHASH%%',
            $hash,
            $urlTemplate
        );

        return (string) Uri::withQueryValues(new Uri($url), [
            'isInvite' => '1',
        ]);
    }
}

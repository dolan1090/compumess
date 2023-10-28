<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Validation;

use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Exception\EmployeeManagementException;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class PreWriteValidationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly EmailValidationService $emailValidationService, private readonly EntityRepository $customerRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [PreWriteValidationEvent::class => 'onPreWriteValidation'];
    }

    public function onPreWriteValidation(PreWriteValidationEvent $event): void
    {
        foreach ($event->getCommands() as $command) {
            if (!$command instanceof InsertCommand && !$command instanceof UpdateCommand) {
                continue;
            }

            $entity = $command->getDefinition()->getEntityName();
            if ($entity !== EmployeeDefinition::ENTITY_NAME) {
                continue;
            }

            $payload = $command->getPayload();
            if (!isset($payload['email'])) {
                continue;
            }

            $email = $payload['email'];
            if (!\is_string($email)) {
                continue;
            }

            $salesChannelId = null;
            $businessPartnerCustomerId = $this->getBusinessPartnerId($command);
            $employeeId = $this->getEmployeeId($command);
            if ($businessPartnerCustomerId !== null) {
                $salesChannelId = $this->getSalesChannelId($businessPartnerCustomerId, $event->getContext());
            }

            if (
                !$this->emailValidationService->validateEmployees($email, $salesChannelId, $employeeId)
                || !$this->emailValidationService->validateCustomers($email, $salesChannelId)
            ) {
                $event->getExceptions()->add(EmployeeManagementException::employeeMailNotUnique());
            }
        }
    }

    private function getBusinessPartnerId(InsertCommand|UpdateCommand $command): ?string
    {
        $payload = $command->getPayload();
        if (!\array_key_exists('business_partner_customer_id', $payload)) {
            return null;
        }

        $businessPartnerCustomerId = $payload['business_partner_customer_id'];
        if (!\is_string($businessPartnerCustomerId)) {
            return null;
        }

        return Uuid::fromBytesToHex($businessPartnerCustomerId);
    }

    private function getEmployeeId(InsertCommand|UpdateCommand $command): ?string
    {
        $primaryKey = $command->getPrimaryKey();
        if (empty($primaryKey)) {
            return null;
        }

        return Uuid::fromBytesToHex(\array_pop($primaryKey));
    }

    private function getSalesChannelId(string $businessPartnerCustomerId, Context $context): ?string
    {
        $salesChannelId = null;
        $customer = $this->customerRepository->search(new Criteria([$businessPartnerCustomerId]), $context)->first();
        if ($customer instanceof CustomerEntity) {
            $salesChannelId = $customer->getBoundSalesChannelId();
        }

        return $salesChannelId;
    }
}

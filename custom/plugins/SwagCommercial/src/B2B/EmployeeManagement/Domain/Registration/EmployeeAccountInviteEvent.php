<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Flag\EmployeeAware;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowMailVariables;
use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\CustomerAware;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\FlowEventAware;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class EmployeeAccountInviteEvent extends Event implements SalesChannelAware, EmployeeAware, CustomerAware, MailAware, ScalarValuesAware, FlowEventAware
{
    public const EVENT_NAME = 'employee.invite';

    public const INVITE_LINK = 'inviteLink';

    private string $shopName;

    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly Context $context,
        private readonly EmployeeEntity $employeeEntity,
        private readonly CustomerEntity $customer,
        private readonly SalesChannelEntity $salesChannel,
        private readonly string $inviteLink
    ) {
        $shopName = $this->salesChannel->getTranslation('name');
        $this->shopName = \is_string($shopName) ? $shopName : '';
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getValues(): array
    {
        return [
            FlowMailVariables::SHOP_NAME => $this->shopName,
            self::INVITE_LINK => $this->inviteLink,
        ];
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('employee', new EntityType(EmployeeDefinition::class))
            ->add('customer', new EntityType(CustomerDefinition::class))
            ->add('inviteLink', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('shopName', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $this->mailRecipientStruct = new MailRecipientStruct([
                $this->employeeEntity->getEmail() => $this->employeeEntity->getEmail(),
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannel->getId();
    }

    public function getInviteLink(): string
    {
        return $this->inviteLink;
    }

    public function getShopName(): string
    {
        return $this->shopName;
    }

    public function getEmployee(): EmployeeEntity
    {
        return $this->employeeEntity;
    }

    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function getCustomerId(): string
    {
        return $this->getCustomer()->getId();
    }

    public function getEmployeeId(): string
    {
        return $this->getEmployee()->getId();
    }
}

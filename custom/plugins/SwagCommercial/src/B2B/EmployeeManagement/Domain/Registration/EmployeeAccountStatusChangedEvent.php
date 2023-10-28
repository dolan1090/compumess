<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Registration;

use Shopware\Commercial\B2B\EmployeeManagement\Domain\Flag\EmployeeAware;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeEntity;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowMailVariables;
use Shopware\Core\Content\Flow\Dispatching\Aware\ScalarValuesAware;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\EventData\ScalarValueType;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class EmployeeAccountStatusChangedEvent extends Event implements SalesChannelAware, EmployeeAware, MailAware, ScalarValuesAware
{
    public const EVENT_NAME = 'employee.status.changed';

    public function __construct(
        private readonly Context $context,
        private readonly SalesChannelEntity $salesChannel,
        private readonly EmployeeEntity $employee,
    ) {
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValues(): array
    {
        return [
            FlowMailVariables::SHOP_NAME => $this->getShopName(),
            'employee' => $this->employee,
        ];
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('employee', new EntityType(EmployeeDefinition::class))
            ->add('shopName', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getMailStruct(): MailRecipientStruct
    {
        return new MailRecipientStruct([
            $this->employee->getEmail() => $this->employee->getEmail(),
        ]);
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannel->getId();
    }

    public function getShopName(): ?string
    {
        /** @var string|null $shopName */
        $shopName = $this->salesChannel->getTranslation('name');

        return $shopName;
    }

    public function getEmployee(): EmployeeEntity
    {
        return $this->employee;
    }

    public function getEmployeeId(): string
    {
        return $this->getEmployee()->getId();
    }
}

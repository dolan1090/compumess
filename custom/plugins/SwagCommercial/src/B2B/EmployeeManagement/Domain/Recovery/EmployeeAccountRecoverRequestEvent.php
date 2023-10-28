<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Domain\Recovery;

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
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('checkout')]
class EmployeeAccountRecoverRequestEvent extends Event implements SalesChannelAware, ShopwareSalesChannelEvent, EmployeeAware, MailAware, ScalarValuesAware
{
    public const EVENT_NAME = 'employee.recovery.request';

    private string $shopName;

    private ?MailRecipientStruct $mailRecipientStruct = null;

    public function __construct(
        private readonly SalesChannelContext $salesChannelContext,
        private readonly EmployeeEntity $employee,
        private readonly string $resetUrl
    ) {
        $shopName = $salesChannelContext->getSalesChannel()->getTranslation('name');
        $this->shopName = \is_string($shopName) ? $shopName : '';
    }

    /**
     * @return array<string, scalar|array<mixed>|null>
     */
    public function getValues(): array
    {
        return [
            FlowMailVariables::RESET_URL => $this->resetUrl,
            FlowMailVariables::SHOP_NAME => $this->shopName,
        ];
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('employee', new EntityType(EmployeeDefinition::class))
            ->add('resetUrl', new ScalarValueType(ScalarValueType::TYPE_STRING))
            ->add('shopName', new ScalarValueType(ScalarValueType::TYPE_STRING));
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $this->mailRecipientStruct = new MailRecipientStruct([
                $this->employee->getEmail() => $this->employee->getEmail(),
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelContext->getSalesChannel()->getId();
    }

    public function getResetUrl(): string
    {
        return $this->resetUrl;
    }

    public function getShopName(): string
    {
        return $this->shopName;
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

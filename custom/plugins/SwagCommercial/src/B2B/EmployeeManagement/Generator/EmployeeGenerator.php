<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\EmployeeManagement\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\B2B\EmployeeManagement\Entity\Employee\EmployeeDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityWriterInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Demodata\DemodataContext;
use Shopware\Core\Framework\Demodata\DemodataGeneratorInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\Test\TestDefaults;

/**
 * @internal
 */
#[Package('checkout')]
class EmployeeGenerator implements DemodataGeneratorInterface
{
    public function __construct(
        private readonly EntityWriterInterface $writer,
        private readonly EmployeeDefinition $employeeDefinition,
        private readonly CustomerDefinition $customerDefinition,
        private readonly Connection $connection,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
    ) {
    }

    public function getDefinition(): string
    {
        return EmployeeDefinition::class;
    }

    public function generate(int $numberOfItems, DemodataContext $context, array $options = []): void
    {
        if ($numberOfItems <= 0) {
            return;
        }

        $context->getConsole()->progressStart($numberOfItems);
        $writeContext = WriteContext::createFromContext($context->getContext());

        $customerId = $this->createBusinessPartner($context, $writeContext);
        $this->createEmployees($customerId, $numberOfItems, $context, $writeContext);

        $context->getConsole()->progressFinish();
    }

    private function createEmployees(string $customerId, int $numberOfItems, DemodataContext $context, WriteContext $writeContext): void
    {
        $console = $context->getConsole();

        $payload = [];

        for ($i = 0; $i < $numberOfItems; ++$i) {
            $payload[] = [
                'businessPartnerCustomerId' => $customerId,
                'active' => true,
                'password' => 'shopware',
                'firstName' => $context->getFaker()->format('firstName'),
                'lastName' => $context->getFaker()->format('lastName'),
                'email' => $context->getFaker()->format('safeEmail'),
            ];
        }

        foreach (array_chunk($payload, 100) as $chunk) {
            $this->writer->upsert($this->employeeDefinition, $chunk, $writeContext);
            $console->progressAdvance(\count($chunk));
        }
    }

    private function createBusinessPartner(DemodataContext $context, WriteContext $writeContext): string
    {
        $id = Uuid::randomHex();
        $firstName = $context->getFaker()->format('firstName');
        $lastName = $context->getFaker()->format('lastName');
        $salutationId = $this->connection->fetchOne('SELECT LOWER(HEX(id)) FROM salutation');

        $addresses = [[
            'id' => Uuid::randomHex(),
            'salutationId' => $salutationId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'street' => $context->getFaker()->format('streetName'),
            'zipcode' => $context->getFaker()->format('postcode'),
            'city' => $context->getFaker()->format('city'),
            'countryId' => $this->connection->fetchOne('SELECT LOWER(HEX(id)) FROM country WHERE active = 1'),
        ]];

        $customer = [
            'id' => $id,
            'customerNumber' => $this->numberRangeValueGenerator->getValue('customer', $context->getContext(), null),
            'salutationId' => $salutationId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $context->getFaker()->format('safeEmail'),
            'password' => 'shopware',
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'defaultPaymentMethodId' => $this->connection->fetchOne('SELECT LOWER(HEX(id)) FROM `payment_method` WHERE `active` = 1 ORDER BY `position` ASC'),
            'salesChannelId' => $this->connection->fetchOne('SELECT LOWER(HEX(id)) FROM sales_channel WHERE type_id = :typeId', [
                'typeId' => Uuid::fromHexToBytes(Defaults::SALES_CHANNEL_TYPE_STOREFRONT),
            ]),
            'defaultBillingAddressId' => $addresses[0]['id'],
            'defaultShippingAddressId' => $addresses[0]['id'],
            'addresses' => $addresses,
            'specificFeatures' => [
                'features' => [
                    'EMPLOYEE_MANAGEMENT' => true,
                ],
            ],
            'b2bBusinessPartner' => [],
        ];

        $this->writer->upsert($this->customerDefinition, [$customer], $writeContext);

        return $id;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ClassificationCustomer\Domain\Customer;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\ClassificationCustomer\Exception\ClassificationCustomerException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @phpstan-type CustomerData array{id: string, last_order_date: ?string, order_count: ?int, customer_number: string,
 *                              first_order_date: ?string, created_at: string, first_login: ?string, last_login: ?string}
 * @phpstan-type GenerateTagsData array{additionInformation: ?string, customerFields: string[], numberOfTags?: ?int,
 *                              formatResponse: string}
 * @phpstan-type ClassifyCustomerData array{groups: array{id: string, name: string, ruleBuilder: string}[],
 *                                     customerIds: string[], formatResponse: string, languageId?: string, locale?: string}
 */
#[Package('checkout')]
class ClassificationCustomerService
{
    private const API_CUSTOMER_CLASSIFICATION_CLASSIFY = 'https://ai-services.apps.shopware.io/api/customer-classification/generate';

    private const API_CUSTOMER_CLASSIFICATION_GENERATE_TAG = 'https://ai-services.apps.shopware.io/api/customer-tag/generate';

    private const CHUNK_SIZE = 1000;

    private const CHUNK_CUSTOMER_SIZE = 100;

    private const FEATURE_TOGGLE_FOR_SERVICE = 'CUSTOMER_CLASSIFICATION-5348744';

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ClientInterface $client,
        private readonly SystemConfigService $configService,
        private readonly EntityRepository $tagRepository
    ) {
    }

    /**
     * @param ClassifyCustomerData $classificationContext
     *
     * @throws GuzzleException
     */
    public function classify(array $classificationContext, Context $context): void
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        if (empty($classificationContext['customerIds'])) {
            return;
        }

        if (isset($classificationContext['languageId'])) {
            $classificationContext['locale'] = $this->getLocale($classificationContext['languageId']);
            unset($classificationContext['languageId']);
        }

        $customers = $this->getCustomers($classificationContext['customerIds']);
        /** @var CustomerData[][] $chunkCustomers */
        $chunkCustomers = array_chunk($customers, self::CHUNK_CUSTOMER_SIZE);

        foreach ($chunkCustomers as $chunkCustomer) {
            $results = $this->classifyChunk($chunkCustomer, $classificationContext);
            $upsertData = [];
            foreach ($results as $tagId => $numbers) {
                $customerIds = $this->getCustomerIdsByNumbers($customers, $numbers);
                if (\count($customerIds) === 0) {
                    continue;
                }

                $customerIds = array_map(function (string $id) {
                    return ['id' => $id];
                }, $customerIds);

                $upsertData[] = [
                    'id' => $tagId,
                    'customers' => $customerIds,
                ];
            }

            if (\count($upsertData)) {
                $this->tagRepository->upsert($upsertData, $context);
            }
        }
    }

    /**
     * @param GenerateTagsData $groups
     *
     * @throws GuzzleException
     *
     * @return array<array<string, string>>
     */
    public function generateTag(array $groups): array
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        try {
            $response = $this->client->request(
                Request::METHOD_POST,
                self::API_CUSTOMER_CLASSIFICATION_GENERATE_TAG,
                [
                    'headers' => [
                        'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
                    ],
                    'json' => $groups,
                ]
            );

            /** @var array<string, string> $responseArray */
            $responseArray = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                \JSON_THROW_ON_ERROR
            );

            if (!isset($responseArray['result'])) {
                return [];
            }

            /** @var array<array<string, string>> $results */
            $results = json_decode($responseArray['result'], true, 512, \JSON_THROW_ON_ERROR);
        } catch (GuzzleException|\JsonException) {
            throw ClassificationCustomerException::generateTagsError();
        }

        return $results;
    }

    private function getLocale(string $languageId): string
    {
        /** @var string|null $locale */
        $locale = $this->connection->fetchOne(
            'SELECT locale.code FROM locale INNER JOIN language ON locale.id = language.locale_id  WHERE language.id = :languageId',
            ['languageId' => Uuid::fromHexToBytes($languageId)]
        );

        if ($locale === null) {
            throw new \InvalidArgumentException(sprintf('Could not find locale for languageId "%s"', $languageId));
        }

        return $locale;
    }

    /**
     * @param string[] $ids
     *
     * @return CustomerData[]
     */
    private function getCustomers(array $ids): array
    {
        $results = [];

        $idsChunks = array_chunk($ids, self::CHUNK_SIZE);

        $sql = 'SELECT
                    DATE_FORMAT(`customer`.`last_order_date`, "%y%m%d") `last_order_date`,
                    LOWER(HEX(`customer`.`id`)) as `id`,
                    `customer`.`order_count`,
                    `customer`.`order_total_amount`,
                    `customer`.`customer_number`,
                    MIN(DATE_FORMAT(`order`.`order_date`, "%y%m%d")) AS `first_order_date`,
                    DATE_FORMAT(`customer`.`created_at`, "%y%m%d") AS `created_at`,
                    DATE_FORMAT(`customer`.`first_login`, "%y%m%d") AS `first_login`,
                    DATE_FORMAT(`customer`.`last_login`, "%y%m%d") AS `last_login`
                FROM
                    `customer`
                        JOIN
                    `order_customer` ON `customer`.`id` = `order_customer`.`customer_id`
                        JOIN
                    `order` ON `order_customer`.`order_id` = `order`.`id`
                WHERE `customer`.`id` IN (:ids)
                GROUP BY `customer`.`id` , `customer`.`first_login` , `customer`.`last_login` , `customer`.`last_order_date` , `customer`.`order_count` , `customer`.`order_total_amount` , `customer`.`created_at` , `customer`.`customer_number`
            ';

        foreach ($idsChunks as $idsChunk) {
            /** @var CustomerData[] $customers */
            $customers = $this->connection->fetchAllAssociative(
                $sql,
                ['ids' => Uuid::fromHexToBytesList($idsChunk)],
                ['ids' => ArrayParameterType::STRING]
            );

            $results = [...$results, ...$customers];
        }

        return $results;
    }

    /**
     * @param CustomerData[] $chunkCustomer
     * @param ClassifyCustomerData $classificationContext
     *
     * @return array<string, string[]>
     */
    private function classifyChunk(array $chunkCustomer, array $classificationContext): array
    {
        try {
            unset($classificationContext['customerIds']);
            foreach ($chunkCustomer as &$customer) {
                unset($customer['id']);
            }

            $response = $this->client->request(Request::METHOD_POST, self::API_CUSTOMER_CLASSIFICATION_CLASSIFY, [
                'json' => [
                    ...$classificationContext,
                    'customers' => $chunkCustomer,
                ],
                'headers' => [
                    'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
                ],
            ]);

            $response = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

            /** @var array<string, string> $response */
            if (!isset($response['result'])) {
                return [];
            }

            /** @var array<string, string[]> $results */
            $results = json_decode($response['result'], true, 512, \JSON_THROW_ON_ERROR);
        } catch (GuzzleException|\JsonException) {
            throw ClassificationCustomerException::classifyCustomersError();
        }

        return $results;
    }

    /**
     * @param CustomerData[] $customers
     * @param string[] $customerNumbers
     *
     * @return string[]
     */
    private function getCustomerIdsByNumbers(array $customers, array $customerNumbers): array
    {
        $ids = [];
        foreach ($customers as $customer) {
            if (\in_array($customer['customer_number'], $customerNumbers, true)) {
                $ids[] = $customer['id'];
            }
        }

        return $ids;
    }
}

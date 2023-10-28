<?php declare(strict_types=1);

namespace Acris\CustomerPrice;

use Acris\ImportExport\AcrisImportExport;
use Acris\ImportExport\AcrisImportExportCS;
use Acris\ImportExport\Components\Process\ProcessService;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Doctrine\DBAL\Connection;
use Shopware\Core\Content\ImportExport\ImportExportProfileEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class AcrisCustomerPriceCS extends Plugin
{
    const DEFAULT_CUSTOMER_PRICES_IMPORT_EXPORT_PROFILE_NAME = 'ACRIS Customer Prices';
    const DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME = "ACRIS-Sync-API-Customer-Prices";

    public function uninstall(UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }
        $this->cleanupDatabase();
        $this->removeImportExportProfiles($context->getContext(), $this->getCriteriaForRemovingImportExportProfiles());
        $this->removeDefaultValuesForImportExportPlugin($context->getContext());
    }

    public function postUpdate(Plugin\Context\UpdateContext $context): void
    {
        if(version_compare($context->getCurrentPluginVersion(), '1.3.0', '<')
            && version_compare($context->getUpdatePluginVersion(), '1.3.0', '>=')) {
            if($context->getPlugin()->isActive() === true) {
                $this->insertDefaultImportExportProfile($context->getContext());
                $this->insertDefaultValuesForImportExportPlugin($context->getContext());
            }
        }

        if(version_compare($context->getCurrentPluginVersion(), '2.2.0', '<')
            && version_compare($context->getUpdatePluginVersion(), '2.2.0', '>=')) {
            if($context->getPlugin()->isActive() === true) {
                $this->insertDefaultValuesForImportExportPlugin($context->getContext());
            }
        }

        if(version_compare($context->getCurrentPluginVersion(), '2.4.1', '<')
            && version_compare($context->getUpdatePluginVersion(), '2.4.1', '>=')) {
            if($context->getPlugin()->isActive() === true) {
                $this->updateConversionFields($context->getContext());
            }
        }

        if(version_compare($context->getCurrentPluginVersion(), '3.0.1', '<')
            && version_compare($context->getUpdatePluginVersion(), '3.0.1', '>=')) {
            if($context->getPlugin()->isActive() === true) {
                $this->updateConversionFields($context->getContext());
            }
        }
    }

    public function activate(Plugin\Context\ActivateContext $context): void
    {
        parent::activate($context);
        $this->insertDefaultImportExportProfile($context->getContext());
        $this->insertDefaultValuesForImportExportPlugin($context->getContext());

    }

    private function cleanupDatabase(): void
    {
        $connection = $this->container->get(Connection::class);
        $connection->executeStatement('ALTER TABLE `product` DROP COLUMN `acrisCustomerPrice`');
        $connection->executeStatement('ALTER TABLE `customer` DROP COLUMN `acrisCustomerPrice`');
        $connection->executeStatement('ALTER TABLE `rule` DROP COLUMN `acrisCustomerPrices`');

        $connection->executeStatement('DROP TABLE IF EXISTS acris_customer_advanced_price');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_customer_price_rule');
        $connection->executeStatement('DROP TABLE IF EXISTS acris_customer_price');
    }

    private function insertDefaultImportExportProfile(Context $context): void
    {
        $importExportProfileRepository = $this->container->get('import_export_profile.repository');

        $defaultImportExportProfiles = [
            [
                'name' => self::DEFAULT_CUSTOMER_PRICES_IMPORT_EXPORT_PROFILE_NAME,
                'label' => self::DEFAULT_CUSTOMER_PRICES_IMPORT_EXPORT_PROFILE_NAME,
                'systemDefault' => false,
                'sourceEntity' => 'acris_customer_price',
                'fileType' => 'text/csv',
                'delimiter' => ';',
                'enclosure' => '"',
                'mapping' => [
                    [
                        "key" =>  "id",
                        "mappedKey" =>  "id",
                        "position" =>  0
                    ],
                    [
                        "key" =>  "productId",
                        "mappedKey" =>  "productId",
                        "position" =>  1
                    ],
                    [
                        "key" =>  "customerId",
                        "mappedKey" =>  "customerId",
                        "position" =>  2
                    ],
                    [
                        "key" =>  "acrisPrices.quantityStart",
                        "mappedKey" =>  "quantityStart",
                        "position" =>  3
                    ],
                    [
                        "key" =>  "acrisPrices.quantityEnd",
                        "mappedKey" =>  "quantityEnd",
                        "position" =>  4
                    ],
                    [
                        "key" =>  "listPriceType",
                        "mappedKey" =>  "listPriceType",
                        "position" =>  5
                    ],
                    [
                        "key" =>  "active",
                        "mappedKey" =>  "active",
                        "position" =>  6
                    ],
                    [
                        "key" =>  "activeFrom",
                        "mappedKey" =>  "activeFrom",
                        "position" =>  7
                    ],
                    [
                        "key" =>  "activeUntil",
                        "mappedKey" =>  "activeUntil",
                        "position" =>  8
                    ],
                    [
                        "key" =>  "ruleIds",
                        "mappedKey" =>  "ruleIds",
                        "position" =>  9
                    ]
                ]
            ]
        ];

        foreach ($defaultImportExportProfiles as $defaultImportExportProfile) {
            $this->createIfNotExists($importExportProfileRepository, [['name' => 'name', 'value' => $defaultImportExportProfile['name']]], $defaultImportExportProfile, $context);
        }
    }

    private function createIfNotExists(EntityRepository $repository, array $equalFields, array $data, Context $context): void
    {
        $filters = [];
        foreach ($equalFields as $equalField) {
            $filters[] = new EqualsFilter($equalField['name'], $equalField['value']);
        }
        if(sizeof($filters) > 1) {
            $filter = new MultiFilter(MultiFilter::CONNECTION_OR, $filters);
        } else {
            $filter = array_shift($filters);
        }

        $searchResult = $repository->search((new Criteria())->addFilter($filter), $context);

        if($searchResult->count() == 0) {
            $repository->create([$data], $context);
        } elseif ($searchResult->count() > 0) {
            $data['id'] = $searchResult->first()->getId();
            $repository->update([$data], $context);
        }
    }

    private function removeImportExportProfiles(Context $context, Criteria $criteria): void
    {
        $connection = $this->container->get(Connection::class);

        $importExportProfileRepository = $this->container->get('import_export_profile.repository');
        $importExportLogRepository = $this->container->get('import_export_log.repository');

        /** @var EntitySearchResult $searchResult */
        $searchResult = $importExportProfileRepository->search($criteria, $context);

        $ids = [];
        /** @var \Shopware\Core\Framework\Uuid\Uuid $uuid */
        $uuid = new \Shopware\Core\Framework\Uuid\Uuid();
        if($searchResult->getTotal() > 0 && $searchResult->first()) {

            /** @var ImportExportProfileEntity $entity */
            foreach ($searchResult->getEntities()->getElements() as $entity) {

                if ($entity->getSystemDefault() === true) {
                    $importExportProfileRepository->update([
                        ['id' => $entity->getId(), 'systemDefault' => false ]
                    ], $context);
                }

                /** @var EntitySearchResult $logResult */
                $logResult = $importExportLogRepository->search((new Criteria())->addFilter(new EqualsFilter('profileId', $entity->getId())), $context);
                if ($logResult->getTotal() > 0 && $logResult->first()) {
                    /** @var ImportExportLogEntity $logEntity */
                    foreach ($logResult->getEntities() as $logEntity) {
                        $stmt = $connection->prepare("UPDATE import_export_log SET profile_id = :profileId WHERE id = :id");
                        $stmt->execute(['profileId' => null, 'id' => $uuid::fromHexToBytes($logEntity->getId()) ]);
                    }
                }

                $ids[] = ['id' => $entity->getId()];
            }
            $importExportProfileRepository->delete($ids, $context);
        }
    }

    private function insertDefaultValuesForImportExportPlugin(Context $context): void
    {
        $kernelPluginCollection = $this->container->get('Shopware\Core\Framework\Plugin\KernelPluginCollection');

        /** @var AcrisImportExport $importExportPlugin */
        $importExportPlugin = $kernelPluginCollection->get(AcrisImportExport::class);

        /** @var AcrisImportExportCS $importExportPlugin */
        $importExportPluginCS = $kernelPluginCollection->get(AcrisImportExportCS::class);

        if (($importExportPlugin === null || $importExportPlugin->isActive() === false) && ($importExportPluginCS === null || $importExportPluginCS->isActive() === false)) {
            return;
        }

        $this->insertDefaultIdentifiers($context);
        $this->insertDefaultReplacements($context);
        $this->insertDefaultProcess($context);
        $this->insertRulesConversionFieldIfNoExists($context);
    }

    private function insertDefaultIdentifiers(Context $context): void
    {
        /** @var EntityRepository $identifierRepository */
        $identifierRepository = $this->container->get('acris_import_export_identifier.repository');

        $defaultIdentifiers = [
            [
                'entity' => 'product',
                'identifier' => 'productNumber',
                'priority' => 10,
                'active' => true
            ],[
                'entity' => 'customer',
                'identifier' => 'customerNumber',
                'priority' => 10,
                'active' => true
            ]
        ];

        foreach ($defaultIdentifiers as $defaultIdentifier) {
            $this->createIdentifierIfNotExists($identifierRepository, $context, $defaultIdentifier);
        }
    }

    private function insertDefaultProcess(Context $context): void
    {
        /** @var EntityRepository $processRepository */
        $processRepository = $this->container->get('acris_import_export_process.repository');
        $filePathSync = $this->getPath() . '/Resources/default-values/import/customer-prices-sync-api/';
        $defaultSyncProcessFields = [
            [
                'name' => 'productId',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'productId.php'),
                'dataType' => 'string',
                'required' => true,
                'addIfNotExists' => true,
                'addingOrder' => 1
            ],[
                'name' => 'customerId',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'customerId.php'),
                'dataType' => 'string',
                'required' => true,
                'addIfNotExists' => true,
                'addingOrder' => 2
            ],[
                'name' => 'addCustomerPriceId',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'addCustomerPriceId.php'),
                'dataType' => 'array',
                'required' => true,
                'addIfNotExists' => true,
                'addingOrder' => 3
            ],[
                'name' => 'listPriceType',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'listPriceType.php'),
                'dataType' => 'string',
                'required' => false,
                'addIfNotExists' => true,
                'addingOrder' => 50
            ],[
                'name' => 'acrisPrices',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'acrisPrices.php'),
                'dataType' => 'array',
                'required' => false,
                'addIfNotExists' => true,
                'addingOrder' => 50
            ],[
                'name' => 'rules',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'rules.php'),
                'dataType' => 'array',
                'required' => false,
                'addIfNotExists' => false
            ]
        ];

        $defaultProcesses = [
            [
                'name' => self::DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME,
                'fileName' => $this->convertProcessNameToIdent( self::DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME ) . ".csv",
                'profileId' => $this->getProfileIdByName($this->convertProcessNameToIdent(self::DEFAULT_CUSTOMER_PRICES_IMPORT_EXPORT_PROFILE_NAME), $context),
                'mode' => 'import',
                'importType' => ProcessService::PROCESS_TYPE_SYNC_API,
                'entity' => 'acris_customer_price',
                'active' => true,
                'processFields' => $defaultSyncProcessFields,
                'sendErrorMail' => true,
                'sorting' => 'modify',
                'isDefault' => true,
                'timeBetweenUploadAndImport' => 30,
                'maxTimeProcessRunning' => 86400,
                'maxFilesForImport' => 10,
                'orderNameForImportedFiles' => 'imported',
                'orderNameForFilesDuringImport' => 'in_progress',
                'maxTimeAfterLastImport' => 604800,
                'maxTimeOfOldImportedFiles' => 2592000
            ]
        ];

        foreach ($defaultProcesses as $defaultProcess) {
            $this->createProcessIfNotExists($processRepository, $context, $defaultProcess);
        }

    }

    private function getProfileIdByName(string $profileName, Context $context): ?string
    {
        $profileRepository = $this->container->get('import_export_profile.repository');
        return $profileRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('name', $profileName)), $context)->firstId();
    }

    private function convertProcessNameToIdent($name )
    {
        return str_replace( '-', '_', $name );
    }

    /**
     * @param EntityRepository $entityRepository
     * @param Context $context
     * @param array $identifierData
     */
    private function createIdentifierIfNotExists(EntityRepository $entityRepository, Context $context, array $identifierData): void
    {
        $exists = $entityRepository->search((new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [new EqualsFilter('entity', $identifierData['entity']), new EqualsFilter('identifier', $identifierData['identifier'])])), $context);
        if($exists->getTotal() === 0) {
            $entityRepository->create([$identifierData], $context);
        }
    }

    /**
     * @param EntityRepository $entityRepository
     * @param Context $context
     * @param array $processData
     */
    private function createProcessIfNotExists(EntityRepository $entityRepository, Context $context, array $processData): void
    {
        $exists = $entityRepository->search((new Criteria())->addFilter(new EqualsFilter('name', $processData['name'])), $context);
        if($exists->getTotal() === 0) {
            $entityRepository->create([$processData], $context);
        }
    }

    private function removeDefaultValuesForImportExportPlugin(Context $context): void
    {
        $kernelPluginCollection = $this->container->get('Shopware\Core\Framework\Plugin\KernelPluginCollection');

        /** @var AcrisImportExport $importExportPlugin */
        $importExportPlugin = $kernelPluginCollection->get(AcrisImportExport::class);

        /** @var AcrisImportExportCS $importExportPlugin */
        $importExportPluginCS = $kernelPluginCollection->get(AcrisImportExportCS::class);

        if (($importExportPlugin === null || $importExportPlugin->isActive() === false) && ($importExportPluginCS === null || $importExportPluginCS->isActive() === false)) {
            return;
        }

        $this->removeDefaultProcess($context);
    }

    private function removeDefaultProcess(Context $context): void
    {
        /** @var EntityRepository $processRepository */
        $processRepository = $this->container->get('acris_import_export_process.repository');

        $searchResult = $processRepository->searchIds((new Criteria())->addFilter(
            new EqualsFilter('name', self::DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME)
        ), $context);

        $ids = [];

        if ($searchResult->getTotal() > 0) {
            foreach ($searchResult->getIds() as $id) {
                $ids[] = ['id' => $id];
            }
            $processRepository->delete($ids, $context);
        }
    }

    private function getCriteriaForRemovingImportExportProfiles(): Criteria
    {
        $criteria = new Criteria();
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsFilter('sourceEntity', 'acris_customer_price')
        ]));

        return $criteria;
    }

    private function insertDefaultReplacements(Context $context)
    {
        /** @var EntityRepository $replacementRepository */
        $replacementRepository = $this->container->get('acris_import_export_replacement.repository');

        $defaultReplacements = [
            [
                'entity' => 'acris_customer_price',
                'propertyName' => 'acrisPrices',
                'active' => true
            ]
        ];

        foreach ($defaultReplacements as $defaultReplacement) {
            $this->createReplacementIfNotExists($replacementRepository, $context, $defaultReplacement);
        }
    }

    /**
     * @param EntityRepository $entityRepository
     * @param Context $context
     * @param array $replacementData
     */
    private function createReplacementIfNotExists(EntityRepository $entityRepository, Context $context, array $replacementData): void
    {
        $exists = $entityRepository->search((new Criteria())->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [new EqualsFilter('entity', $replacementData['entity']), new EqualsFilter('propertyName', $replacementData['propertyName'])])), $context);
        if($exists->getTotal() === 0) {
            $entityRepository->create([$replacementData], $context);
        }
    }

    private function insertRulesConversionFieldIfNoExists(Context $context): void
    {
        /** @var EntityRepository $processFieldRepository */
        $processFieldRepository = $this->container->get('acris_import_export_process_field.repository');

        $criteria = new Criteria();
        $criteria->addAssociation('process');
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('process.name', self::DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME),
            new EqualsFilter('name', 'rules')
        ]));

        $id = $processFieldRepository->searchIds($criteria, $context)->firstId();
        if (!empty($id)) return;

        $processId = $this->getProcessId($context);
        if (empty($processId)) return;

        $filePathSync = $this->getPath() . '/Resources/default-values/import/customer-prices-sync-api/';

        $processFieldRepository->upsert([
            [
                'processId' => $processId,
                'name' => 'rules',
                'active' => true,
                'conversion' => file_get_contents($filePathSync.'rules.php'),
                'dataType' => 'array',
                'required' => false,
                'addIfNotExists' => false
            ]
        ], $context);
    }

    private function getProcessId(Context $context): ?string
    {
        /** @var EntityRepository $processRepository */
        $processRepository = $this->container->get('acris_import_export_process.repository');

        return $processRepository->searchIds((new Criteria())->addFilter(new EqualsFilter('name', self::DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME)), $context)->firstId();
    }

    private function updateConversionFields(Context $context): void
    {
        $kernelPluginCollection = $this->container->get('Shopware\Core\Framework\Plugin\KernelPluginCollection');

        /** @var AcrisImportExport $importExportPlugin */
        $importExportPlugin = $kernelPluginCollection->get(AcrisImportExport::class);

        /** @var AcrisImportExportCS $importExportPlugin */
        $importExportPluginCS = $kernelPluginCollection->get(AcrisImportExportCS::class);

        if (($importExportPlugin === null || $importExportPlugin->isActive() === false) && ($importExportPluginCS === null || $importExportPluginCS->isActive() === false)) {
            return;
        }

        $this->updateProcessField($context, 'acrisPrices');
        $this->updateProcessField($context, 'customerId');
        $this->updateProcessField($context, 'productId');
    }

    private function updateProcessField(Context $context, string $fieldName): void
    {
        /** @var EntityRepository $processFieldRepository */
        $processFieldRepository = $this->container->get('acris_import_export_process_field.repository');
        /** @var IdSearchResult $processFieldIdResult */
        $processFieldIdResult = $processFieldRepository->searchIds((new Criteria())->addAssociation('process')->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [new EqualsFilter('process.name', self::DEFAULT_SYNC_API_CUSTOMER_PRICES_IMPORT_NAME), new EqualsFilter('name', $fieldName)])), $context);
        if($processFieldIdResult->firstId()) {
            $filePathSync = $this->getPath() . '/Resources/default-values/import/customer-prices-sync-api/';
            $processFieldRepository->upsert([
                [
                    'id' => $processFieldIdResult->firstId(),
                    'conversion' => file_get_contents($filePathSync.$fieldName.'.php')
                ]
            ], $context);
        }
    }
}

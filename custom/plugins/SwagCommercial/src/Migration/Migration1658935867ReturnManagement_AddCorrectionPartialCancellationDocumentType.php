<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\Document\PartialCancellationRenderer;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\Translations;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1658935867ReturnManagement_AddCorrectionPartialCancellationDocumentType extends MigrationStep
{
    use ImportTranslationsTrait;
    final public const DOCUMENT_TYPE_NAME_EN = 'Partial cancellation';

    final public const DOCUMENT_TYPE_NAME_DE = 'Teilstornierung';

    final public const NUMBER_RANGE_NAME = 'document_partial_cancellation';

    public function getCreationTimestamp(): int
    {
        return 1658935867;
    }

    public function update(Connection $connection): void
    {
        $this->createDocumentConfiguration($connection);
        $this->createNumberRange($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // TODO: Implement updateDestructive() method.
    }

    private function createNumberRange(Connection $connection): void
    {
        $isNumberRangeTypeExist = $connection->fetchOne(
            'SELECT `id` FROM `number_range_type` WHERE `technical_name` = :technical_name LIMIT 1',
            [
                'technical_name' => self::NUMBER_RANGE_NAME,
            ]
        );

        if ($isNumberRangeTypeExist) {
            return;
        }

        $definitionNumberRangeTypes = [
            self::NUMBER_RANGE_NAME => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameDe' => self::DOCUMENT_TYPE_NAME_DE,
                'nameEn' => self::DOCUMENT_TYPE_NAME_EN,
            ],
        ];

        foreach ($definitionNumberRangeTypes as $typeName => $numberRangeType) {
            $connection->insert(
                'number_range_type',
                [
                    'id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'global' => $numberRangeType['global'],
                    'technical_name' => $typeName,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $translation = new Translations(
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameDe'],
                ],
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameEn'],
                ]
            );

            $this->importTranslation('number_range_type_translation', $translation, $connection);
        }

        $definitionNumberRanges = [
            self::NUMBER_RANGE_NAME => [
                'id' => Uuid::randomHex(),
                'nameEn' => self::DOCUMENT_TYPE_NAME_EN,
                'nameDe' => self::DOCUMENT_TYPE_NAME_DE,
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes[self::NUMBER_RANGE_NAME]['id'],
                'pattern' => '{n}',
                'start' => 1000,
            ],
        ];

        foreach ($definitionNumberRanges as $numberRange) {
            $connection->insert(
                'number_range',
                [
                    'id' => Uuid::fromHexToBytes($numberRange['id']),
                    'global' => $numberRange['global'],
                    'type_id' => Uuid::fromHexToBytes($numberRange['typeId']),
                    'pattern' => $numberRange['pattern'],
                    'start' => $numberRange['start'],
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $translation = new Translations(
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['nameDe'],
                ],
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['nameEn'],
                ]
            );

            $this->importTranslation('number_range_translation', $translation, $connection);
        }
    }

    private function createDocumentConfiguration(Connection $connection): void
    {
        $isDocumentTypeExist = $connection->fetchOne(
            'SELECT `id` FROM `document_type` WHERE `technical_name` = :technical_name LIMIT 1',
            [
                'technical_name' => PartialCancellationRenderer::TYPE,
            ]
        );

        if ($isDocumentTypeExist) {
            return;
        }

        $correctionInvoiceId = Uuid::randomBytes();

        $connection->insert('document_type', ['id' => $correctionInvoiceId, 'technical_name' => PartialCancellationRenderer::TYPE, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $translation = new Translations(
            [
                'document_type_id' => $correctionInvoiceId,
                'name' => self::DOCUMENT_TYPE_NAME_DE,
            ],
            [
                'document_type_id' => $correctionInvoiceId,
                'name' => self::DOCUMENT_TYPE_NAME_EN,
            ]
        );

        $this->importTranslation('document_type_translation', $translation, $connection);

        $defaultConfig = [
            'displayPrices' => true,
            'displayFooter' => true,
            'displayHeader' => true,
            'displayLineItems' => true,
            'diplayLineItemPosition' => true,
            'displayPageCount' => true,
            'displayCompanyAddress' => true,
            'pageOrientation' => 'portrait',
            'pageSize' => 'a4',
            'itemsPerPage' => 10,
            'companyName' => 'Example Company',
            'taxNumber' => '',
            'vatId' => '',
            'taxOffice' => '',
            'bankName' => '',
            'bankIban' => '',
            'bankBic' => '',
            'placeOfJurisdiction' => '',
            'placeOfFulfillment' => '',
            'executiveDirector' => '',
            'companyAddress' => '',
        ];

        $correctionInvoiceConfig = $defaultConfig;
        $correctionInvoiceConfig['referencedDocumentType'] = PartialCancellationRenderer::TYPE;

        $correctInvoiceConfigId = Uuid::randomBytes();

        $connection->insert(
            'document_base_config',
            [
                'id' => $correctInvoiceConfigId,
                'name' => PartialCancellationRenderer::TYPE,
                'global' => 1, 'filename_prefix' => PartialCancellationRenderer::TYPE . '_',
                'document_type_id' => $correctionInvoiceId, 'config' => json_encode($correctionInvoiceConfig, \JSON_THROW_ON_ERROR),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
        $connection->insert(
            'document_base_config_sales_channel',
            [
                'id' => Uuid::randomBytes(),
                'document_base_config_id' => $correctInvoiceConfigId,
                'document_type_id' => $correctionInvoiceId,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );
    }
}

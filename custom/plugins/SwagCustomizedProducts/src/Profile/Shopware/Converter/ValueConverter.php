<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Shopware\Converter;

use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\ValueDataSet;
use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DataSet\DataSet;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Logging\Log\EmptyNecessaryFieldRunLog;
use SwagMigrationAssistant\Migration\Logging\LoggingServiceInterface;
use SwagMigrationAssistant\Migration\Mapping\MappingServiceInterface;
use SwagMigrationAssistant\Migration\Media\MediaFileServiceInterface;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Converter\ShopwareConverter;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\MediaDataSet;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class ValueConverter extends ShopwareConverter
{
    protected Context $context;

    protected string $connectionId;

    protected ?string $currencyUuid = null;

    protected string $runId;

    protected string $locale;

    public function __construct(
        MappingServiceInterface $mappingService,
        LoggingServiceInterface $loggingService,
        protected readonly MediaFileServiceInterface $mediaFileService
    ) {
        parent::__construct($mappingService, $loggingService);
    }

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        // Take care that you specify the supports function the same way that you have in your reader
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getDataSet() instanceof DataSet
            && $migrationContext->getDataSet()::getEntity() === ValueDataSet::getEntity();
    }

    /**
     * @param array<string, int|string> $data
     */
    public function getSourceIdentifier(array $data): string
    {
        return (string) $data['id'];
    }

    /**
     * @param array<string, mixed> $converted
     *
     * @return array<int, mixed>|null
     */
    public function getMediaUuids(array $converted): ?array
    {
        $mediaUuids = [];
        foreach ($converted as $data) {
            if (!isset($data['media']['id'])) {
                continue;
            }

            $mediaUuids[] = $data['media']['id'];
        }

        return $mediaUuids;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function convert(array $data, Context $context, MigrationContextInterface $migrationContext): ConvertStruct
    {
        // Generate a checksum for the data to allow faster migrations in the future
        $this->generateChecksum($data);

        $this->context = $context;
        $this->locale = $data['_locale'];
        $this->runId = $migrationContext->getRunUuid();

        $connection = $migrationContext->getConnection();
        $this->connectionId = '';
        if ($connection !== null) {
            $this->connectionId = $connection->getId();
        }

        // Get uuid for value entity out of mapping table or create a new one
        $this->mainMapping = $this->mappingService->getOrCreateMapping(
            $this->connectionId,
            ValueDataSet::getEntity(),
            $data['id'],
            $this->context,
            $this->checksum
        );
        $converted['id'] = $this->mainMapping['entityUuid'];

        // This method checks if key is available in data array and set value in converted array
        $this->convertValue($converted, 'itemNumber', $data, 'ordernumber');
        $this->convertValue($converted, 'default', $data, 'is_default_value', self::TYPE_BOOLEAN);
        $this->convertValue($converted, 'position', $data, 'position', self::TYPE_INTEGER);
        $this->convertValue($converted, 'oneTimeSurcharge', $data, 'is_once_surcharge', self::TYPE_BOOLEAN);

        $value = $data['value'];
        if (isset($data['media'])) {
            $converted['media'] = $this->getMedia($data);
            $value = $converted['media']['id'];
        }

        $converted['value'] = [
            '_value' => $value,
        ];

        // Get associated uuid of option out of mapping table
        $mapping = $this->mappingService->getMapping(
            $this->connectionId,
            OptionDataSet::getEntity(),
            $data['option_id'],
            $this->context
        );

        if ($mapping === null) {
            $this->loggingService->addLogEntry(new EmptyNecessaryFieldRunLog(
                $migrationContext->getRunUuid(),
                ValueDataSet::getEntity(),
                $data['id'],
                'option_id'
            ));

            return new ConvertStruct(null, $data);
        }

        $converted['templateOptionId'] = $mapping['entityUuid'];

        $price = $data['price'] ?? null;
        if ($price) {
            $converted['tax'] = $this->getTax($price['tax']);

            if ($price['is_percentage_surcharge']) {
                $converted['relativeSurcharge'] = true;
                $converted['percentageSurcharge'] = $price['percentage'];
            } else {
                $converted['relativeSurcharge'] = false;
                $converted['price'] = $this->getPrice($data, $converted['tax']['taxRate']);
            }
        }

        $this->getTranslation($data, $converted);
        $this->updateMainMapping($migrationContext, $this->context);

        // Unset used data keys
        unset(
            // Used
            $data['id'],
            $data['option_id'],
            $data['media_id'],
            $data['media'],
            $data['value'],
            $data['price'],
            $data['_locale'],
            $data['currencyShortName']
        );

        if (empty($data)) {
            $data = null;
        }

        return new ConvertStruct($converted, $data, $this->mainMapping['id'] ?? null);
    }

    /**
     * Called to write the created mapping to mapping table
     */
    public function writeMapping(Context $context): void
    {
        $this->mappingService->writeMapping($context);
    }

    /**
     * @psalm-suppress TypeDoesNotContainType
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $converted
     */
    protected function getTranslation(array &$data, array &$converted): void
    {
        $language = $this->mappingService->getDefaultLanguage($this->context);
        if (!$language instanceof LanguageEntity) {
            return;
        }

        $defaultLanguageUuid = $language->getId();
        $converted['translations'][$defaultLanguageUuid] = [];
        $this->convertValue($converted['translations'][$defaultLanguageUuid], 'displayName', $data, 'name', self::TYPE_STRING);
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     *
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function getMedia(array $data): array
    {
        $newMedia = [];
        $mapping = $this->mappingService->getOrCreateMapping(
            $this->connectionId,
            DefaultEntities::MEDIA,
            $data['media']['id'],
            $this->context
        );
        $newMedia['id'] = $mapping['entityUuid'];
        $this->mappingIds[] = $mapping['id'];

        if (empty($data['media']['name'])) {
            $data['media']['name'] = $newMedia['id'];
        }

        $this->mediaFileService->saveMediaFile(
            [
                'runId' => $this->runId,
                'entity' => MediaDataSet::getEntity(),
                'uri' => $data['media']['uri'] ?? $data['media']['path'],
                'fileName' => $data['media']['name'],
                'fileSize' => (int) $data['media']['file_size'],
                'mediaId' => $newMedia['id'],
            ]
        );

        $this->getMediaTranslation($newMedia, $data);
        $this->convertValue($newMedia, 'title', $data['media'], 'name');
        $this->convertValue($newMedia, 'alt', $data['media'], 'description');

        $albumMapping = $this->mappingService->getMapping(
            $this->connectionId,
            DefaultEntities::MEDIA_FOLDER,
            $data['media']['albumID'],
            $this->context
        );

        if ($albumMapping !== null) {
            $newMedia['mediaFolderId'] = $albumMapping['entityUuid'];
            $this->mappingIds[] = $albumMapping['id'];
        }

        return $newMedia;
    }

    /**
     * @param array<string, mixed> $media
     * @param array<string, mixed> $data
     */
    protected function getMediaTranslation(array &$media, array $data): void
    {
        $language = $this->mappingService->getDefaultLanguage($this->context);
        if (!$language instanceof LanguageEntity) {
            return;
        }

        $locale = $language->getLocale();
        if (!$locale instanceof LocaleEntity || $locale->getCode() === $this->locale) {
            return;
        }

        $localeTranslation = [];

        $this->convertValue($localeTranslation, 'title', $data['media'], 'name');
        $this->convertValue($localeTranslation, 'alt', $data['media'], 'description');

        $mapping = $this->mappingService->getOrCreateMapping(
            $this->connectionId,
            DefaultEntities::MEDIA_TRANSLATION,
            $data['media']['id'] . ':' . $this->locale,
            $this->context
        );
        $localeTranslation['id'] = $mapping['entityUuid'];
        $this->mappingIds[] = $mapping['id'];

        $languageUuid = $this->mappingService->getLanguageUuid($this->connectionId, $this->locale, $this->context);

        if ($languageUuid !== null) {
            $localeTranslation['languageId'] = $languageUuid;
            $media['translations'][$languageUuid] = $localeTranslation;
        }
    }

    /**
     * @param array<string, mixed> $taxData
     *
     * @return array<string, mixed>
     */
    private function getTax(array $taxData): array
    {
        $taxRate = (float) $taxData['tax'];
        $taxUuid = $this->mappingService->getTaxUuid($this->connectionId, $taxRate, $this->context);

        if (empty($taxUuid)) {
            $mapping = $this->mappingService->getOrCreateMapping(
                $this->connectionId,
                DefaultEntities::TAX,
                $taxData['id'],
                $this->context
            );
            $taxUuid = $mapping['entityUuid'];
            $this->mappingIds[] = $mapping['id'];
        }

        return [
            'id' => $taxUuid,
            'taxRate' => $taxRate,
            'name' => $taxData['description'],
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, mixed>
     */
    private function getPrice(array $data, float $taxRate): array
    {
        $net = $data['price']['surcharge'];
        $gross = \round((float) $net * (1 + $taxRate / 100), $this->context->getRounding()->getDecimals());

        if (isset($data['currencyShortName']['currencyShortName'])) {
            $currencyMapping = $this->mappingService->getMapping(
                $this->connectionId,
                DefaultEntities::CURRENCY,
                $data['currencyShortName']['currencyShortName'],
                $this->context
            );
        }
        if (!isset($currencyMapping)) {
            return [];
        }
        $this->currencyUuid = $currencyMapping['entityUuid'];
        $this->mappingIds[] = $currencyMapping['id'];

        $price = [];
        if ($this->currencyUuid !== Defaults::CURRENCY) {
            $price[] = [
                'currencyId' => Defaults::CURRENCY,
                'gross' => $gross,
                'net' => (float) $net,
                'linked' => true,
            ];
        }

        $price[] = [
            'currencyId' => $this->currencyUuid,
            'gross' => $gross,
            'net' => (float) $net,
            'linked' => true,
        ];

        return $price;
    }
}

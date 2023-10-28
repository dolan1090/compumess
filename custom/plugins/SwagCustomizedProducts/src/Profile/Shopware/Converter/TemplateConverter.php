<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Shopware\Converter;

use Shopware\Core\Framework\Context;
use Shopware\Core\System\Language\LanguageEntity;
use Shopware\Core\System\Locale\LocaleEntity;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DataSet\DataSet;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Logging\Log\CannotConvertChildEntity;
use SwagMigrationAssistant\Migration\Logging\LoggingServiceInterface;
use SwagMigrationAssistant\Migration\Mapping\MappingServiceInterface;
use SwagMigrationAssistant\Migration\Media\MediaFileServiceInterface;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Converter\ShopwareConverter;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\MediaDataSet;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class TemplateConverter extends ShopwareConverter
{
    protected string $connectionId;

    protected Context $context;

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
            && $migrationContext->getDataSet()::getEntity() === TemplateDataSet::getEntity();
    }

    /**
     * @param array<string, int|string> $data
     */
    public function getSourceIdentifier(array $data): string
    {
        return (string) $data['id'];
    }

    /**
     * @param array<array<string, mixed>> $converted
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

        // Get uuid for template entity out of mapping table or create a new one
        $this->mainMapping = $this->mappingService->getOrCreateMapping(
            $this->connectionId,
            TemplateDataSet::getEntity(),
            $data['id'],
            $this->context,
            $this->checksum
        );
        $converted['id'] = $this->mainMapping['entityUuid'];

        // This method checks if key is available in data array and set value in converted array
        $this->convertValue($converted, 'internalName', $data, 'internal_name');
        $this->convertValue($converted, 'stepByStep', $data, 'step_by_step_configurator', self::TYPE_BOOLEAN);
        $this->convertValue($converted, 'active', $data, 'active', self::TYPE_BOOLEAN);
        $this->convertValue($converted, 'confirmInput', $data, 'confirm_input', self::TYPE_BOOLEAN);

        if (isset($data['media'])) {
            $this->getMedia($converted, $data);
        }

        if (isset($data['productIds'])) {
            $products = $this->getProducts($data);

            if (!empty($products)) {
                $converted['products'] = $products;
            }
        }

        $this->getTranslation($data, $converted);
        $this->updateMainMapping($migrationContext, $this->context);

        // Unset used data keys
        unset(
            // Used
            $data['id'],
            $data['media_id'],
            $data['media'],
            $data['_locale'],
            $data['products']
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
     * @psalm-suppress PossiblyInvalidArgument
     *
     * @param array<string, mixed> $converted
     * @param array<string, mixed> $data
     */
    protected function getMedia(array &$converted, array $data): void
    {
        if (!isset($data['media']['id'])) {
            $this->loggingService->addLogEntry(new CannotConvertChildEntity(
                $this->runId,
                DefaultEntities::MEDIA,
                TemplateDataSet::getEntity(),
                $data['id']
            ));

            return;
        }

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

        $converted['media'] = $newMedia;
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
        $this->convertValue($converted['translations'][$defaultLanguageUuid], 'displayName', $data, 'display_name', self::TYPE_STRING);
        $this->convertValue($converted['translations'][$defaultLanguageUuid], 'description', $data, 'description', self::TYPE_STRING);
    }

    /**
     * Get converted products
     *
     * @param array<string, mixed> $data
     *
     * @return array<int, mixed>
     */
    private function getProducts(array $data): array
    {
        $products = [];
        foreach ($data['productIds'] as $productId) {
            // Get associated uuid of product out of mapping table
            $mapping = $this->mappingService->getMapping(
                $this->connectionId,
                DefaultEntities::PRODUCT_MAIN,
                $productId,
                $this->context
            );

            if ($mapping === null) {
                $mapping = $this->mappingService->getMapping(
                    $this->connectionId,
                    DefaultEntities::PRODUCT_CONTAINER,
                    $productId,
                    $this->context
                );
            }

            if ($mapping === null) {
                continue;
            }

            $productUuid = $mapping['entityUuid'];
            $newProduct['id'] = $productUuid;
            $products[] = $newProduct;
        }

        return $products;
    }
}

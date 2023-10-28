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
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Checkbox;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\DateTime;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\HtmlEditor;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\NumberField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Textarea;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\TextField;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Timestamp;
use SwagMigrationAssistant\Migration\Connection\SwagMigrationConnectionEntity;
use SwagMigrationAssistant\Migration\Converter\ConvertStruct;
use SwagMigrationAssistant\Migration\DataSelection\DataSet\DataSet;
use SwagMigrationAssistant\Migration\DataSelection\DefaultEntities;
use SwagMigrationAssistant\Migration\Logging\Log\EmptyNecessaryFieldRunLog;
use SwagMigrationAssistant\Migration\Logging\Log\ExceptionRunLog;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\Converter\ShopwareConverter;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class OptionConverter extends ShopwareConverter
{
    private const OPTION_TYPE_TIME = 'time';

    private const OPTION_TYPE_DATE = 'date';

    private const OPTION_TYPE_HTML_EDITOR = 'wysiwyg';

    private const OPTION_TYPE_RADIO = 'radio';

    private const OPTION_TYPE_MULTISELECT = 'multiselect';

    private const DEFAULT_MIN_VALUE = 0;

    private const DEFAULT_MAX_VALUE = 1000000000;

    protected Context $context;

    protected string $connectionId;

    protected ?string $currencyUuid = null;

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        // Take care that you specify the supports function the same way that you have in your reader
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getDataSet() instanceof DataSet
            && $migrationContext->getDataSet()::getEntity() === OptionDataSet::getEntity();
    }

    /**
     * @param array<string, int|string> $data
     */
    public function getSourceIdentifier(array $data): string
    {
        return (string) $data['id'];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function convert(array $data, Context $context, MigrationContextInterface $migrationContext): ConvertStruct
    {
        // Generate a checksum for the data to allow faster migrations in the future
        $this->generateChecksum($data);

        $this->context = $context;

        $connection = $migrationContext->getConnection();
        $this->connectionId = '';
        if ($connection instanceof SwagMigrationConnectionEntity) {
            $this->connectionId = $connection->getId();
        }

        // Get uuid for option entity out of mapping table or create a new one
        $this->mainMapping = $this->mappingService->getOrCreateMapping(
            $this->connectionId,
            OptionDataSet::getEntity(),
            $data['id'],
            $this->context,
            $this->checksum
        );
        $converted['id'] = $this->mainMapping['entityUuid'];

        // This method checks if key is available in data array and set value in converted array
        $ordernumber = isset($data['value']['ordernumber']) ? $data['value']['ordernumber'] : $data['ordernumber'];
        $converted['itemNumber'] = $ordernumber;
        $this->convertValue($converted, 'position', $data, 'position', self::TYPE_INTEGER);
        $this->convertValue($converted, 'oneTimeSurcharge', $data, 'is_once_surcharge', self::TYPE_BOOLEAN);

        $converted['type'] = $this->convertType($data['type']);

        if ($data['type'] !== Checkbox::NAME) {
            $this->convertValue($converted, 'required', $data, 'required', self::TYPE_BOOLEAN);
        }

        $converted['typeProperties'] = $this->convertTypeProperties($data, $migrationContext->getRunUuid());

        // Get associated uuid of template out of mapping table
        $mapping = $this->mappingService->getMapping(
            $this->connectionId,
            TemplateDataSet::getEntity(),
            $data['template_id'],
            $this->context
        );

        if ($mapping === null) {
            $this->loggingService->addLogEntry(new EmptyNecessaryFieldRunLog(
                $migrationContext->getRunUuid(),
                OptionDataSet::getEntity(),
                $data['id'],
                'template_id'
            ));

            return new ConvertStruct(null, $data);
        }

        $converted['templateId'] = $mapping['entityUuid'];

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

        $valuePrice = isset($data['value']['price']) ? $data['value']['price'] : null;
        if ($valuePrice) {
            if ($valuePrice['is_percentage_surcharge']) {
                $converted['percentageSurcharge'] = isset($converted['percentageSurcharge']) ? $converted['percentageSurcharge'] + $valuePrice['percentage'] : $valuePrice['percentage'];
            } else {
                $converted['tax'] ??= $this->getTax($valuePrice['tax']);
                $price = $this->getPrice($data['value'], $converted['tax']['taxRate']);

                if (empty($converted['price'])) {
                    $converted['price'] = $price;
                }

                $converted['price'][0]['gross'] += $price[0]['gross'];
                $converted['price'][0]['net'] += $price[0]['net'];
            }
        }

        $this->getTranslation($data, $converted);
        $this->updateMainMapping($migrationContext, $this->context);

        // Unset used data keys
        unset(
            // Used
            $data['id'],
            $data['template_id'],
            $data['name'],
            $data['type'],
            $data['ordernumber'],
            $data['max_text_length'],
            $data['max_file_size'],
            $data['max_files'],
            $data['price'],
            $data['value'],
            $data['currencyShortName'],
            $data['min_value'],
            $data['max_value']
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
        $name = isset($data['value']['name']) ? $data['value']['name'] : $data['name'];
        $converted['translations'][$defaultLanguageUuid]['displayName'] = $name;
        $this->convertValue($converted['translations'][$defaultLanguageUuid], 'description', $data, 'description', self::TYPE_STRING);
        $this->convertValue($converted['translations'][$defaultLanguageUuid], 'placeholder', $data, 'placeholder', self::TYPE_STRING);
    }

    private function convertType(string $type): string
    {
        switch ($type) {
            case self::OPTION_TYPE_TIME:
                return Timestamp::NAME;
            case self::OPTION_TYPE_DATE:
                return DateTime::NAME;
            case self::OPTION_TYPE_HTML_EDITOR:
                return HtmlEditor::NAME;
            case self::OPTION_TYPE_RADIO:
            case self::OPTION_TYPE_MULTISELECT:
                return Select::NAME;
            default:
                return $type;
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function convertTypeProperties(array $data, string $runId): array
    {
        $typeProperties = [];

        switch ($data['type']) {
            case NumberField::NAME:
                $data['min_value'] = $data['min_value'] ?? self::DEFAULT_MIN_VALUE;
                $data['max_value'] = $data['max_value'] ?? self::DEFAULT_MAX_VALUE;
                $this->convertValue($typeProperties, 'interval', $data, 'interval', self::TYPE_INTEGER);
                $this->convertValue($typeProperties, 'defaultValue', $data, 'default_value', self::TYPE_INTEGER);
                $this->convertValue($typeProperties, 'minValue', $data, 'min_value');
                $this->convertValue($typeProperties, 'maxValue', $data, 'max_value');

                break;

            case TextField::NAME:
            case Textarea::NAME:
                $typeProperties['minLength'] = 0;
                $typeProperties['maxLength'] = (int) $data['max_text_length'];

                break;

            case FileUpload::NAME:
            case ImageUpload::NAME:
                $this->convertValue($typeProperties, 'maxFileSize', $data, 'max_file_size', self::TYPE_INTEGER);
                $this->convertValue($typeProperties, 'maxCount', $data, 'max_files', self::TYPE_INTEGER);

                break;

            case self::OPTION_TYPE_TIME:
                $this->convertTime($typeProperties, 'startTime', $data, 'min_date', $runId);
                $this->convertTime($typeProperties, 'endTime', $data, 'max_date', $runId);

                break;

            case self::OPTION_TYPE_DATE:
                $this->convertValue($typeProperties, 'minDate', $data, 'min_date', self::TYPE_DATETIME);
                $this->convertValue($typeProperties, 'maxDate', $data, 'max_date', self::TYPE_DATETIME);

                break;

            case self::OPTION_TYPE_MULTISELECT:
                $typeProperties['isMultiSelect'] = true;

                break;
        }

        if ($data['allows_multiple_selection']) {
            $typeProperties['isMultiSelect'] = true;
        }

        return $typeProperties;
    }

    /**
     * @param array<string, mixed> $newData
     * @param array<string, mixed> $sourceData
     */
    private function convertTime(array &$newData, string $newKey, array &$sourceData, string $sourceKey, string $runId): void
    {
        if (!isset($sourceData[$sourceKey])) {
            return;
        }

        try {
            $date = new \DateTime($sourceData[$sourceKey]);

            $time = $date->format('H:i:s');

            $newData[$newKey] = $time;
        } catch (\Exception $e) {
            $this->loggingService->addLogEntry(new ExceptionRunLog(
                $runId,
                OptionDataSet::getEntity(),
                $e,
                'template_id'
            ));
        }

        unset($sourceData[$sourceKey]);
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

<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Shopware\DataSelection;

use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\ValueDataSet;
use SwagMigrationAssistant\Migration\DataSelection\DataSelectionInterface;
use SwagMigrationAssistant\Migration\DataSelection\DataSelectionStruct;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\CrossSellingDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\MainVariantRelationDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ManufacturerAttributeDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\MediaFolderDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ProductAttributeDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ProductDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ProductOptionRelationDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ProductPriceAttributeDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\ProductPropertyRelationDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\PropertyGroupOptionDataSet;
use SwagMigrationAssistant\Profile\Shopware\DataSelection\DataSet\TranslationDataSet;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class CustomProductDataSelection implements DataSelectionInterface
{
    final public const IDENTIFIER = 'customProducts';

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface;
    }

    public function getData(): DataSelectionStruct
    {
        return new DataSelectionStruct(
            self::IDENTIFIER,
            $this->getDataSets(),
            $this->getDataSetsRequiredForCount(),
            'swag-migration.index.selectDataCard.dataSelection.customProducts',
            500,
            true,
            DataSelectionStruct::PLUGIN_DATA_TYPE
        );
    }

    public function getDataSets(): array
    {
        return [
            new MediaFolderDataSet(),
            new ProductAttributeDataSet(),
            new ProductPriceAttributeDataSet(),
            new ManufacturerAttributeDataSet(),
            new ProductDataSet(),
            new PropertyGroupOptionDataSet(),
            new ProductOptionRelationDataSet(),
            new ProductPropertyRelationDataSet(),
            new TranslationDataSet(),
            new CrossSellingDataSet(),
            new MainVariantRelationDataSet(),
            new TemplateDataSet(),
            new OptionDataSet(),
            new ValueDataSet(),
        ];
    }

    public function getDataSetsRequiredForCount(): array
    {
        return [
            new TemplateDataSet(),
        ];
    }
}

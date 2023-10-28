<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ColorSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\FileUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageSelect;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\ImageUpload;
use Swag\CustomizedProducts\Template\Aggregate\TemplateOption\Type\Select;
use Swag\CustomizedProducts\Template\TemplateEntity;

class TemplateConfigurationService implements TemplateConfigurationServiceInterface
{
    private const selectionTypes = [Select::NAME, ColorSelect::NAME, ImageSelect::NAME];
    private const uploadTypes = [ImageUpload::NAME, FileUpload::NAME];

    public function __construct(
        private readonly EntityRepository $configurationRepository,
        private readonly EntityRepository $templateRepository
    ) {
    }

    public function getTemplateConfiguration(LineItem $customizedProductLineItem, string $currentConfigurationHash, Context $context): ?TemplateConfigurationEntity
    {
        $configuration = $this->getConfigurationByHash($currentConfigurationHash, $customizedProductLineItem->getQuantity(), $context);
        if ($configuration !== null) {
            return $configuration;
        }

        $id = Uuid::randomHex();
        $this->configurationRepository->create(
            [
                [
                    'id' => $id,
                    'templateId' => $customizedProductLineItem->getReferencedId(),
                    'hash' => $customizedProductLineItem->getPayloadValue(
                        CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH
                    ),
                    'configuration' => $this->getConfigurationArray($customizedProductLineItem),
                ],
            ],
            $context
        );

        return $this->configurationRepository->search(new Criteria([$id]), $context)->get($id);
    }

    public function isConfigurationFullyRestorable(TemplateConfigurationEntity $configuration, Context $context): bool
    {
        $template = $this->getTemplateForComparison($configuration->getTemplateId(), $context);
        if ($template === null) {
            return false;
        }

        $options = $template->getOptions();
        if ($options === null) {
            return true;
        }

        foreach ($configuration->getConfiguration() as $id => $value) {
            if (!\is_string($id) || $id === 'quantity') {
                continue;
            }

            $option = $options->get($id);
            if ($option === null) {
                return false;
            }

            if (!\array_key_exists('type', $value) || !\array_key_exists('value', $value)) {
                continue;
            }

            $type = $value['type'];
            $configurationValues = $value['value'];
            if (\in_array($type, self::selectionTypes, true)) {
                $optionValues = $option->getValues();
                if ($optionValues === null) {
                    return false;
                }

                foreach ($configurationValues as $configurationId) {
                    if (!\in_array($configurationId, $optionValues->getIds(), true)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    private function getConfigurationArray(LineItem $customizedProductLineItem): array
    {
        $configuration = [
            'quantity' => $customizedProductLineItem->getQuantity(),
        ];
        $optionLineItems = $customizedProductLineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_LINE_ITEM_TYPE
        );
        foreach ($optionLineItems as $optionLineItem) {
            $type = $optionLineItem->getPayloadValue('type');
            if ($type === null) {
                continue;
            }

            $referencedId = $optionLineItem->getReferencedId();
            if ($referencedId === null) {
                continue;
            }

            $configuration[$referencedId] = [
                'type' => $type,
                'value' => $this->getOptionValue($optionLineItem, $type),
            ];
        }

        return $configuration;
    }

    /**
     * @return array|string|null
     */
    private function getOptionValue(LineItem $optionLineItem, string $type)
    {
        if (\in_array($type, self::selectionTypes, true)) {
            return $this->getSelectionValue($optionLineItem);
        }

        if (\in_array($type, self::uploadTypes, true)) {
            return $this->getUploadValue($optionLineItem);
        }

        return $optionLineItem->getPayloadValue('value');
    }

    private function getSelectionValue(LineItem $selectionLineItem): array
    {
        $value = [];
        $optionValues = $selectionLineItem->getChildren()->filterType(
            CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_OPTION_VALUE_LINE_ITEM_TYPE
        );
        foreach ($optionValues as $optionValue) {
            $value[] = $optionValue->getReferencedId();
        }

        return $value;
    }

    private function getUploadValue(LineItem $uploadLineItem): array
    {
        $value = [];
        $medias = $uploadLineItem->getPayloadValue('media');

        foreach ($medias as $media) {
            $value[] = $media;
        }

        return $value;
    }

    private function getConfigurationByHash(string $currentConfigurationHash, int $quantity, Context $context): ?TemplateConfigurationEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(
            new EqualsFilter('hash', $currentConfigurationHash)
        );

        /** @var TemplateConfigurationEntity|null $config */
        $config = $this->configurationRepository->search($criteria, $context)->first();
        if ($config === null) {
            return null;
        }

        $configuration = $config->getConfiguration();
        if (!\array_key_exists('quantity', $configuration)) {
            return $config;
        }

        $configuration['quantity'] = $quantity;
        $this->configurationRepository->update(
            [
                [
                    'id' => $config->getId(),
                    'configuration' => $configuration,
                ],
            ],
            $context
        );

        /** @var TemplateConfigurationEntity|null $config */
        $config = $this->configurationRepository->search($criteria, $context)->first();

        return $config;
    }

    private function getTemplateForComparison(string $templateId, Context $context): ?TemplateEntity
    {
        $criteria = new Criteria([$templateId]);
        $criteria->addAssociation('options.values');

        /** @var TemplateEntity|null $template */
        $template = $this->templateRepository->search($criteria, $context)->first();

        return $template;
    }
}

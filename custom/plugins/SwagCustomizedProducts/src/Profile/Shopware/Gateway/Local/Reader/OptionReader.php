<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Shopware\Gateway\Local\Reader;

use Doctrine\DBAL\ArrayParameterType;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\OptionDataSet;
use SwagMigrationAssistant\Migration\DataSelection\DataSet\DataSet;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Migration\TotalStruct;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\Reader\AbstractReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class OptionReader extends AbstractReader
{
    public function supportsTotal(MigrationContextInterface $migrationContext): bool
    {
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getGateway()->getName() === ShopwareLocalGateway::GATEWAY_NAME;
    }

    public function readTotal(MigrationContextInterface $migrationContext): ?TotalStruct
    {
        $this->setConnection($migrationContext);

        $total = (int) $this->connection->createQueryBuilder()
            ->select('COUNT(*)')
            ->from('s_plugin_custom_products_option')
            ->executeQuery()
            ->fetchOne();

        return new TotalStruct(OptionDataSet::getEntity(), $total);
    }

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        // Make sure that this reader is only called for the OptionDataSet entity
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getGateway()->getName() === ShopwareLocalGateway::GATEWAY_NAME
            && $migrationContext->getDataSet() instanceof DataSet
            && $migrationContext->getDataSet()::getEntity() === OptionDataSet::getEntity();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function read(MigrationContextInterface $migrationContext, array $params = []): array
    {
        $this->setConnection($migrationContext);
        $ids = $this->fetchIdentifiers('s_plugin_custom_products_option', $migrationContext->getOffset(), $migrationContext->getLimit());
        $fetchedOptions = $this->fetchData($ids, $migrationContext);
        $options = $this->mapData($fetchedOptions, [], ['templateOption']);

        $optionValues = $this->fetchOptionValues($ids);
        $optionValues = $this->mapData($optionValues, [], ['value']);
        $optionValues = $this->formatOptionValues($optionValues);
        $formattedOptions = [];

        foreach ($options as $option) {
            $values = $optionValues[$option['id']] ?? null;

            if ($values === null) {
                $formattedOptions[] = $option;

                continue;
            }

            $optionId = $option['id'];
            foreach ($values as $value) {
                $option['id'] = $optionId . '_option_' . $value['id'];
                $option['value'] = $value;
                $formattedOptions[] = $option;
            }
        }

        return $this->cleanupResultSet($formattedOptions);
    }

    /**
     * @param array<int, mixed> $optionValues
     *
     * @return array<int|string, array<int, mixed>>
     */
    private function formatOptionValues(array $optionValues): array
    {
        $data = [];

        foreach ($optionValues as $optionValue) {
            $data[$optionValue['option_id']][] = $optionValue;
        }

        return $data;
    }

    /**
     * @param array<int, string> $ids
     *
     * @return array<int, mixed>
     */
    private function fetchOptionValues(array $ids): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_plugin_custom_products_value', 'value');
        $this->addTableSelection($query, 's_plugin_custom_products_value', 'value');

        $query->leftJoin('value', 's_plugin_custom_products_option', 'value_option', 'value.option_id = value_option.id');

        $query->leftJoin('value', 's_plugin_custom_products_price', 'value_price', 'value.id = value_price.value_id');
        $this->addTableSelection($query, 's_plugin_custom_products_price', 'value_price');

        $query->leftJoin('value_price', 's_core_tax', 'value_price_tax', 'value_price.tax_id = value_price_tax.id');
        $this->addTableSelection($query, 's_core_tax', 'value_price_tax');

        $query->leftJoin('value_price', 's_core_currencies', 'currency', 'currency.standard = 1');
        $query->addSelect('currency.currency as currencyShortName');

        $query->where('value.option_id IN (:ids)');
        $query->andWhere('value_option.type = \'checkbox\'');
        $query->setParameter('ids', $ids, ArrayParameterType::INTEGER);

        $query = $query->executeQuery();

        return $query->fetchAllAssociative();
    }

    /**
     * @param array<int, string> $ids
     *
     * @return array<int, mixed>
     */
    private function fetchData(array $ids, MigrationContextInterface $migrationContext): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_plugin_custom_products_option', 'templateOption');
        $this->addTableSelection($query, 's_plugin_custom_products_option', 'templateOption');

        $query->leftJoin('templateOption', 's_plugin_custom_products_price', 'templateOption_price', 'templateOption.id = templateOption_price.option_id');
        $this->addTableSelection($query, 's_plugin_custom_products_price', 'templateOption_price');

        $query->leftJoin('templateOption_price', 's_core_tax', 'templateOption_price_tax', 'templateOption_price.tax_id = templateOption_price_tax.id');
        $this->addTableSelection($query, 's_core_tax', 'templateOption_price_tax');

        $query->leftJoin('templateOption_price', 's_core_currencies', 'currency', 'currency.standard = 1');
        $query->addSelect('currency.currency as currencyShortName');

        $query->where('templateOption.id IN (:ids)');
        $query->setParameter('ids', $ids, ArrayParameterType::INTEGER);

        $query->setFirstResult($migrationContext->getOffset());
        $query->setMaxResults($migrationContext->getLimit());

        $query = $query->executeQuery();

        return $query->fetchAllAssociative();
    }
}

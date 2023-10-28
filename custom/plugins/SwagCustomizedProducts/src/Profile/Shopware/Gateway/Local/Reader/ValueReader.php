<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Shopware\Gateway\Local\Reader;

use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\ValueDataSet;
use SwagMigrationAssistant\Migration\DataSelection\DataSet\DataSet;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Migration\TotalStruct;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\Reader\AbstractReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class ValueReader extends AbstractReader
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
            ->from('s_plugin_custom_products_value')
            ->executeQuery()
            ->fetchOne();

        return new TotalStruct(ValueDataSet::getEntity(), $total);
    }

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        // Make sure that this reader is only called for the ValueDataSet entity
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getGateway()->getName() === ShopwareLocalGateway::GATEWAY_NAME
            && $migrationContext->getDataSet() instanceof DataSet
            && $migrationContext->getDataSet()::getEntity() === ValueDataSet::getEntity();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function read(MigrationContextInterface $migrationContext, array $params = []): array
    {
        $this->setConnection($migrationContext);
        $fetchedValues = $this->fetchData($migrationContext);
        $values = $this->mapData($fetchedValues, [], ['value']);
        $locale = $this->getDefaultShopLocale();

        foreach ($values as &$value) {
            $value['_locale'] = \str_replace('_', '-', $locale);
        }
        unset($value);

        return $this->cleanupResultSet($values);
    }

    /**
     * @return array<int, mixed>
     */
    private function fetchData(MigrationContextInterface $migrationContext): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_plugin_custom_products_value', 'value');
        $this->addTableSelection($query, 's_plugin_custom_products_value', 'value');

        $query->leftJoin('value', 's_plugin_custom_products_option', 'value_option', 'value.option_id = value_option.id');

        $query->leftJoin('value', 's_media', 'media', 'value.media_id = media.id');
        $this->addTableSelection($query, 's_media', 'media');

        $query->leftJoin('value', 's_plugin_custom_products_price', 'value_price', 'value.id = value_price.value_id');
        $this->addTableSelection($query, 's_plugin_custom_products_price', 'value_price');

        $query->leftJoin('value_price', 's_core_tax', 'value_price_tax', 'value_price.tax_id = value_price_tax.id');
        $this->addTableSelection($query, 's_core_tax', 'value_price_tax');

        $query->leftJoin('value_price', 's_core_currencies', 'currency', 'currency.standard = 1');
        $query->addSelect('currency.currency as currencyShortName');

        $query->where('value_option.type != \'checkbox\'');

        $query->setFirstResult($migrationContext->getOffset());
        $query->setMaxResults($migrationContext->getLimit());

        $query = $query->executeQuery();

        return $query->fetchAllAssociative();
    }
}

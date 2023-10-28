<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Profile\Shopware\Gateway\Local\Reader;

use Doctrine\DBAL\ArrayParameterType;
use Swag\CustomizedProducts\Profile\Shopware\DataSelection\DataSet\TemplateDataSet;
use SwagMigrationAssistant\Migration\DataSelection\DataSet\DataSet;
use SwagMigrationAssistant\Migration\MigrationContextInterface;
use SwagMigrationAssistant\Migration\TotalStruct;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\Reader\AbstractReader;
use SwagMigrationAssistant\Profile\Shopware\Gateway\Local\ShopwareLocalGateway;
use SwagMigrationAssistant\Profile\Shopware\ShopwareProfileInterface;

class TemplateReader extends AbstractReader
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
            ->from('s_plugin_custom_products_template')
            ->executeQuery()
            ->fetchOne();

        return new TotalStruct(TemplateDataSet::getEntity(), $total);
    }

    public function supports(MigrationContextInterface $migrationContext): bool
    {
        // Make sure that this reader is only called for the TemplateDataSet entity
        return $migrationContext->getProfile() instanceof ShopwareProfileInterface
            && $migrationContext->getGateway()->getName() === ShopwareLocalGateway::GATEWAY_NAME
            && $migrationContext->getDataSet() instanceof DataSet
            && $migrationContext->getDataSet()::getEntity() === TemplateDataSet::getEntity();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    public function read(MigrationContextInterface $migrationContext, array $params = []): array
    {
        $this->setConnection($migrationContext);
        // Fetch the ids of the given table with the given offset and limit
        $ids = $this->fetchIdentifiers('s_plugin_custom_products_template', $migrationContext->getOffset(), $migrationContext->getLimit());

        $fetchedTemplates = $this->fetchData($ids);
        $templates = $this->mapData($fetchedTemplates, [], ['template']);
        $templateProducts = $this->fetchTemplateProducts($ids);
        $locale = $this->getDefaultShopLocale();

        foreach ($templates as &$template) {
            $template['_locale'] = \str_replace('_', '-', $locale);

            if (isset($templateProducts[$template['id']])) {
                $template['productIds'] = $templateProducts[$template['id']];
            }
        }
        unset($template);

        return $this->cleanupResultSet($templates);
    }

    /**
     * @param array<int, string> $ids
     *
     * @return array<int, mixed>
     */
    private function fetchData(array $ids): array
    {
        $sql = <<<SQL
SELECT
            template.id AS "template.id",
           template.internal_name AS "template.internal_name",
           template.display_name AS "template.display_name",
           template.description AS "template.description",
           template.step_by_step_configurator AS "template.step_by_step_configurator",
           template.active AS "template.active",
           template.confirm_input AS "template.confirm_input",
           media.id AS "template_media.id",
           media.name AS "template_media.name",
           media.description AS "template_media.description",
           media.path AS "template_media.path",
           media.file_size AS "template_media.file_size",
           media.albumID AS "template_media.albumID",
           mediaAttr.id AS "template_media.attribute"
    FROM s_plugin_custom_products_template AS template
          LEFT JOIN s_media AS media ON media.id = template.media_id
          LEFT JOIN s_media_attributes AS mediaAttr ON mediaAttr.mediaID = media.id
    WHERE template.id IN (:ids)
ORDER BY "template.id"
SQL;

        return $this->connection->fetchAllAssociative(
            $sql,
            ['ids' => $ids],
            ['ids' => ArrayParameterType::INTEGER]
        );
    }

    /**
     * @param array<int, string> $ids
     *
     * @return array<int|string, array<int, mixed>>
     */
    private function fetchTemplateProducts(array $ids): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_plugin_custom_products_template_product_relation', 'templateProducts');
        $this->addTableSelection($query, 's_plugin_custom_products_template_product_relation', 'templateProducts');

        $query->where('templateProducts.template_id IN (:ids)');
        $query->setParameter('ids', $ids, ArrayParameterType::INTEGER);

        $templateProducts = $query->executeQuery()->fetchAllAssociative();

        $result = [];
        foreach ($templateProducts as $templateProduct) {
            $result[$templateProduct['templateProducts.template_id']][] = $templateProduct['templateProducts.article_id'];
        }

        return $result;
    }
}

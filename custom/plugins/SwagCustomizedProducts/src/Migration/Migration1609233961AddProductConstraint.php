<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\Migration\MigrationStep;
use Swag\CustomizedProducts\Template\TemplateDefinition;

class Migration1609233961AddProductConstraint extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1609233961;
    }

    public function update(Connection $connection): void
    {
        $this->unlinkNoneExistingTemplates($connection);

        $sql = <<<SQL
ALTER TABLE `product` ADD CONSTRAINT `fk.swag_cupr_template_product.id`
    FOREIGN KEY (`swag_customized_products_template_id`, `swag_customized_products_template_version_id`)
    REFERENCES `swag_customized_products_template` (`id`, `version_id`) ON DELETE SET NULL ON UPDATE CASCADE;
SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function unlinkNoneExistingTemplates(Connection $connection): void
    {
        $sql = \str_replace(
            ['#table#', '#referenceTable#', '#referenceField#'],
            [ProductDefinition::ENTITY_NAME, TemplateDefinition::ENTITY_NAME, Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_FK_COLUMN],
            'SELECT `id` FROM `#table#` WHERE `#referenceField#` IS NOT NULL AND `#referenceField#` NOT IN (SELECT `id` FROM `#referenceTable#`);'
        );

        $ids = $connection->executeQuery($sql)->fetchFirstColumn();
        foreach ($ids as $id) {
            $connection->update(
                ProductDefinition::ENTITY_NAME,
                [
                    Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_FK_COLUMN => null,
                    Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_REFERENCE_VERSION_COLUMN => null,
                    Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN => null,
                ],
                [
                    'id' => $id,
                ]
            );
        }
    }
}

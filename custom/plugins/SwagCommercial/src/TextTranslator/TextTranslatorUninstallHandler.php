<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\System\UninstallHandler;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

#[Package('inventory')]
class TextTranslatorUninstallHandler implements UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void
    {
        if ($context->keepUserData()) {
            return;
        }

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);

        $this->dropTextTranslatorTables($connection);
    }

    private function dropTextTranslatorTables(Connection $connection): void
    {
        $connection->executeStatement(
            'DROP TABLE IF EXISTS
                `product_review_translation`;

            DELETE FROM `migration` WHERE `class` like :modules_name;',
            [
                'modules_name' => 'Migration1681389311AddProductReviewTranslationEntities',
            ]
        );
    }
}

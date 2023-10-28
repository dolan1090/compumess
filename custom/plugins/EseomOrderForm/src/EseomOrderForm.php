<?php declare(strict_types=1);

namespace Eseom\OrderForm;

use Doctrine\DBAL\Connection;
//use Shopware\Core\Framework\DataAbstractionLayer\Indexing\Indexer\InheritanceIndexer;
//use Shopware\Core\Framework\DataAbstractionLayer\Indexing\MessageQueue\IndexerMessageSender;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;

class EseomOrderForm extends Plugin
{
    public function activate(ActivateContext $activateContext): void
    {
    }

    public function uninstall(UninstallContext $context): void
    {
        parent::uninstall($context);

        if ($context->keepUserData()) {
            return;
        }

        /*
        $connection = $this->container->get(Connection::class);

        $connection->executeUpdate('DROP TABLE IF EXISTS `faq_product`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `faq_translation`');
        $connection->executeUpdate('DROP TABLE IF EXISTS `faq`');
        $connection->executeUpdate('ALTER TABLE `product` DROP COLUMN `faqs`');
         */
    }
}

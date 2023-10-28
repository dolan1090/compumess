<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Swag\DynamicAccess\DataAbstractionLayer\CategoryRule\CategoryRuleDefinition;
use Swag\DynamicAccess\DataAbstractionLayer\LandingPageRule\LandingPageRuleDefinition;
use Swag\DynamicAccess\DataAbstractionLayer\ProductRule\ProductRuleDefinition;

class SwagDynamicAccess extends Plugin
{
    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $this->removeTables();
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);
    }

    private function removeTables(): void
    {
        $connection = $this->container->get(Connection::class);

        $classNames = [
            CategoryRuleDefinition::ENTITY_NAME,
            LandingPageRuleDefinition::ENTITY_NAME,
            ProductRuleDefinition::ENTITY_NAME,
        ];

        foreach ($classNames as $className) {
            $connection->executeStatement(\sprintf('DROP TABLE IF EXISTS `%s`', $className));
        }
    }
}

<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\Plugin\Context\UpdateContext;
use Swag\CustomizedProducts\Profile\MigrationAssistantExtensionCompilerPass;
use Swag\CustomizedProducts\Util\Lifecycle\Uninstaller;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SwagCustomizedProducts extends Plugin
{
    final public const CURRENT_API_VERSION = 3;

    private const SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_READ_PRIVILEGE = 'swag_customized_products_template:read';

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new MigrationAssistantExtensionCompilerPass());
    }

    public function activate(ActivateContext $activateContext): void
    {
        parent::activate($activateContext);

        $this->addCustomPrivileges();
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
        parent::deactivate($deactivateContext);

        $this->removeCustomPrivileges();
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        \assert($this->container instanceof ContainerInterface, 'Container is not set yet, please call setContainer() before calling boot(), see src/Core/Kernel.php:186.');

        /** @var EntityRepository $mediaFolderRepository */
        $mediaFolderRepository = $this->container->get('media_folder.repository');
        /** @var EntityRepository $mediaRepository */
        $mediaRepository = $this->container->get('media.repository');
        /** @var EntityRepository $mediaDefaultFolderRepository */
        $mediaDefaultFolderRepository = $this->container->get('media_default_folder.repository');
        /** @var EntityRepository $mediaFolderConfigRepository */
        $mediaFolderConfigRepository = $this->container->get('media_folder_configuration.repository');
        /** @var Connection $connection */
        $connection = $this->container->get(Connection::class);

        $uninstaller = new Uninstaller(
            $mediaFolderRepository,
            $mediaRepository,
            $mediaDefaultFolderRepository,
            $mediaFolderConfigRepository,
            $connection
        );
        $uninstaller->uninstall($uninstallContext->getContext());
    }

    public function update(UpdateContext $updateContext): void
    {
        parent::update($updateContext);

        $this->addCustomPrivileges();
    }

    public function enrichPrivileges(): array
    {
        return [
            'product.viewer' => [
                self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_READ_PRIVILEGE,
            ],
        ];
    }

    private function addCustomPrivileges(): void
    {
        // If either the old behaviour does not exist or the new one already does return
        if (!\method_exists($this, 'addPrivileges')) {
            return;
        }

        $this->addPrivileges(
            'product.viewer',
            [
                self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_READ_PRIVILEGE,
            ]
        );
    }

    private function removeCustomPrivileges(): void
    {
        if (!\method_exists($this, 'removePrivileges')) {
            return;
        }

        $this->removePrivileges([
            self::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_READ_PRIVILEGE,
        ]);
    }
}

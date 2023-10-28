<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher;

use Shopware\Core\Framework\Plugin;
use SwagPublisher\Common\ActionSetUpCompilerPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SwagPublisher extends Plugin
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new ActionSetUpCompilerPass());

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/VersionControlSystem/'));
        $loader->load('version-control-system.xml');
    }
}

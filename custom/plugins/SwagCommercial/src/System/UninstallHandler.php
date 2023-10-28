<?php declare(strict_types=1);

namespace Shopware\Commercial\System;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('core')]
interface UninstallHandler
{
    public function uninstall(ContainerInterface $container, UninstallContext $context): void;
}

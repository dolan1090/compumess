<?php declare(strict_types=1);

namespace Shopware\Commercial\System;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
#[Package('core')]
interface ActivateHandler
{
    public function activate(ContainerInterface $container, ActivateContext $context): void;
}

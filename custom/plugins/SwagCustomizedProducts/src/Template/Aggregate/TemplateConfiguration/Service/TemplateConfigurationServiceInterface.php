<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\Context;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;

interface TemplateConfigurationServiceInterface
{
    public function getTemplateConfiguration(LineItem $customizedProductLineItem, string $currentConfigurationHash, Context $context): ?TemplateConfigurationEntity;

    public function isConfigurationFullyRestorable(TemplateConfigurationEntity $configuration, Context $context): bool;
}

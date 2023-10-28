<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                    add(TemplateConfigurationEntity $entity)
 * @method void                                    set(string $key, TemplateConfigurationEntity $entity)
 * @method \Generator<TemplateConfigurationEntity> getIterator()
 * @method TemplateConfigurationEntity[]           getElements()
 * @method TemplateConfigurationEntity|null        get(string $key)
 * @method TemplateConfigurationEntity|null        first()
 * @method TemplateConfigurationEntity|null        last()
 */
class TemplateConfigurationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateConfigurationEntity::class;
    }
}

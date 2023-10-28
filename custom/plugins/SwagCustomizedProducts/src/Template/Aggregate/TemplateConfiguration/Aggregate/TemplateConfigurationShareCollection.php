<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Aggregate;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                                         add(TemplateConfigurationShareEntity $entity)
 * @method void                                         set(string $key, TemplateConfigurationShareEntity $entity)
 * @method \Generator<TemplateConfigurationShareEntity> getIterator()
 * @method TemplateConfigurationShareEntity[]           getElements()
 * @method TemplateConfigurationShareEntity|null        get(string $key)
 * @method TemplateConfigurationShareEntity|null        first()
 * @method TemplateConfigurationShareEntity|null        last()
 */
class TemplateConfigurationShareCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TemplateConfigurationShareEntity::class;
    }
}

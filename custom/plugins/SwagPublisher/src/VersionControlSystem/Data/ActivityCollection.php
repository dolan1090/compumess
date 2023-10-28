<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Data;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Struct\ArrayEntity;
use SwagPublisher\Common\EntityCollectionAddon;

/**
 * @extends EntityCollection<ArrayEntity>
 *
 * @method void             add(ArrayEntity $entity)
 * @method void             set(string $key, ArrayEntity $entity)
 * @method ArrayEntity[]    getIterator()
 * @method ArrayEntity[]    getElements()
 * @method ArrayEntity|null get(string $key)
 * @method ArrayEntity|null first()
 * @method ArrayEntity|null last()
 */
class ActivityCollection extends EntityCollection
{
    use EntityCollectionAddon;

    public function getApiAlias(): string
    {
        return 'cms_page_activity_collection';
    }

    protected function getExpectedClass(): string
    {
        return ArrayEntity::class;
    }
}

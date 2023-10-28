<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\Common;

use Shopware\Core\Framework\Struct\ArrayEntity;

trait EntityCollectionAddon
{
    public function asDelete(): array
    {
        return $this->forUpdate([]);
    }

    public function forUpdate(array $with): array
    {
        return \array_values($this->map(static function (ArrayEntity $entity) use ($with): array {
            return \array_merge(['id' => $entity->getId()], $with);
        }));
    }
}

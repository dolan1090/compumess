<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\Internal\AffectedEntity;

class AffectedEntityTest extends TestCase
{
    public function testGetIdAndNameFromAffectedEntity(): void
    {
        $id = Uuid::randomHex();
        $name = 'Entity';

        $affectedEntity = AffectedEntity::create($id, $name);

        static::assertSame($id, $affectedEntity->getId());
        static::assertSame($name, $affectedEntity->getName());
        static::assertInstanceOf(AffectedEntity::class, $affectedEntity);
    }
}

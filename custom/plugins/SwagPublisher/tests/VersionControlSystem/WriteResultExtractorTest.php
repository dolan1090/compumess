<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlotTranslation\CmsSlotTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\Internal\WriteResultExtractor;

class WriteResultExtractorTest extends TestCase
{
    public function testWriteResultIsATranslation(): void
    {
        $writeResult = new EntityWriteResult([
            'cmsSlotId' => Uuid::randomHex(),
            'languageId' => Uuid::randomHex(),
        ], [], CmsSlotTranslationDefinition::ENTITY_NAME, EntityWriteResult::OPERATION_UPDATE);

        static::assertTrue(WriteResultExtractor::isTranslation($writeResult));
    }

    public function testWriteResultIsNotATranslation(): void
    {
        $writeResult = new EntityWriteResult(
            Uuid::randomHex(),
            [],
            CmsSlotDefinition::ENTITY_NAME,
            EntityWriteResult::OPERATION_UPDATE
        );

        static::assertFalse(WriteResultExtractor::isTranslation($writeResult));
    }

    public function testExtractAffectedEntityWithSinglePrimaryKey(): void
    {
        $id = Uuid::randomHex();
        $writeResult = new EntityWriteResult(
            $id,
            [],
            CmsSlotDefinition::ENTITY_NAME,
            EntityWriteResult::OPERATION_UPDATE
        );

        $affectedEntity = WriteResultExtractor::extractAffectedEntity($writeResult);
        static::assertSame($id, $affectedEntity->getId());
        static::assertSame(CmsSlotDefinition::ENTITY_NAME, $affectedEntity->getName());
    }

    public function testExtractAffectedEntityWithMultiplePrimaryKeys(): void
    {
        $id = Uuid::randomHex();
        $writeResult = new EntityWriteResult([
            'cmsSlotId' => $id,
            'languageId' => Uuid::randomHex(),
        ], [], CmsSlotTranslationDefinition::ENTITY_NAME, EntityWriteResult::OPERATION_UPDATE);

        $affectedEntity = WriteResultExtractor::extractAffectedEntity($writeResult);
        static::assertSame($id, $affectedEntity->getId());
        static::assertNotSame(CmsSlotTranslationDefinition::ENTITY_NAME, $affectedEntity->getName());
        static::assertSame(CmsSlotDefinition::ENTITY_NAME, $affectedEntity->getName());
    }

    public function testThrowExceptionBecauseOfBadMethodCall(): void
    {
        $id = Uuid::randomHex();
        $writeResult = new EntityWriteResult([
            'foo' => $id,
            'bar' => Uuid::randomHex(),
        ], [], CmsSlotTranslationDefinition::ENTITY_NAME, EntityWriteResult::OPERATION_UPDATE);

        self::expectException(\BadMethodCallException::class);
        self::expectExceptionMessage('Unable to extract affected entity for given write result');
        WriteResultExtractor::extractAffectedEntity($writeResult);
    }
}

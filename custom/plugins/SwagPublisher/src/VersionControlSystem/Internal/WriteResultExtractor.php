<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;

class WriteResultExtractor
{
    private const NEEDLE_ID = 'Id';
    private const NEEDLE_TRANSLATION = '_translation';
    private const LANGUAGE_ID = 'languageId';

    public static function extractAffectedEntity(EntityWriteResult $writeResult): AffectedEntity
    {
        $primaryKey = $writeResult->getPrimaryKey();
        $entityName = $writeResult->getEntityName();

        if (!\is_array($primaryKey)) {
            return AffectedEntity::create($primaryKey, $entityName);
        }

        return self::mapAffectedParentEntity($primaryKey, $entityName);
    }

    public static function isTranslation(EntityWriteResult $writeResult): bool
    {
        $primaryKey = $writeResult->getPrimaryKey();
        $entityName = $writeResult->getEntityName();

        if (!\is_array($primaryKey)) {
            return false;
        }

        return self::extendsLanguageId($primaryKey) && self::extendsTranslation($entityName);
    }

    private static function mapAffectedParentEntity(array $primaryKeys, string $entityName): AffectedEntity
    {
        $affectedEntityName = $entityName = \str_replace(self::NEEDLE_TRANSLATION, '', $entityName);
        $entityName = \str_replace('_', '', $entityName);

        foreach ($primaryKeys as $key => $id) {
            $key = \str_replace(self::NEEDLE_ID, '', $key);

            if (\mb_strtolower($key) === $entityName) {
                return AffectedEntity::create($id, $affectedEntityName);
            }
        }

        throw new \BadMethodCallException('Unable to extract affected entity for given write result');
    }

    private static function extendsTranslation(string $entityName): bool
    {
        return \mb_substr($entityName, -\mb_strlen(self::NEEDLE_TRANSLATION)) === self::NEEDLE_TRANSLATION;
    }

    private static function extendsLanguageId(array $primaryKeys): bool
    {
        return \array_key_exists(self::LANGUAGE_ID, $primaryKeys);
    }
}

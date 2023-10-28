<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\Common;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Uuid\Uuid;

class UpdateChangeContextExtension extends Struct
{
    private const NAME = 'swag.publisher.common-update-change-detector';

    /**
     * @var array<string, bool>
     */
    private array $changes = [];

    /**
     * @var EntityDefinition[]
     */
    private array $definitions = [];

    public static function extract(Context $context): self
    {
        if (!$context->hasExtension(self::NAME)) {
            $context->addExtension(self::NAME, new self());
        }

        /** @var self $extension */
        $extension = $context->getExtension(self::NAME);

        return $extension;
    }

    public function addResult(UpdateCommand $updateCommand, bool $changed): void
    {
        $definition = $updateCommand->getDefinition();

        $this->definitions[$definition->getEntityName()] = $definition;

        $key = $this->generateKey($definition->getEntityName(), $updateCommand->getPrimaryKey());

        if (isset($this->changes[$key]) && $this->changes[$key] === true) {
            return;
        }

        $this->changes[$key] = $changed;
    }

    public function hasChanges(EntityWriteResult $writeResult): bool
    {
        $entityName = $writeResult->getEntityName();

        if (!isset($this->definitions[$entityName])) {
            return false;
        }

        $definition = $this->definitions[$entityName];
        $pks = $definition->getPrimaryKeys();

        $primaryKeyData = [];
        foreach ($pks as $field) {
            if (!$field instanceof StorageAware) {
                continue;
            }

            $name = $field->getPropertyName();
            $property = $writeResult->getProperty($name);

            if (!$property) {
                continue;
            }

            $primaryKeyData[$field->getStorageName()] = $property;
        }

        $key = $this->generateKey($writeResult->getEntityName(), $primaryKeyData);

        return isset($this->changes[$key]) && $this->changes[$key];
    }

    private function generateKey(string $entityName, array $primaryKeys): string
    {
        $serializedData = '';
        \ksort($primaryKeys);

        foreach ($primaryKeys as $name => $value) {
            if (\mb_strlen($value, '8bit') === 16) {
                $value = Uuid::fromBytesToHex($value);
            }

            $serializedData .= $name . ':' . $value . ';';
        }

        return $entityName . '__' . $serializedData;
    }
}

<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisher\VersionControlSystem\Internal;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteContext;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Core\Framework\Uuid\Uuid;
use SwagPublisher\VersionControlSystem\Exception\NotFoundException;

class VersionControlService
{
    private const CMS_IDENTIFIER_KEYS = [
        'id', 'versionId', 'cmsPageVersionId', 'cmsSlotVersionId', '_uniqueIdentifier',
        'cmsSectionVersionId', 'sectionId', 'cmsBlockVersionId', 'cmsSlotId', 'backgroundMediaId',
        'cmsPageId',
    ];

    private DefinitionInstanceRegistry $instanceRegistry;

    private VersionFromVersionUpdater $versionFromVersionUpdater;

    public function __construct(
        DefinitionInstanceRegistry $instanceRegistry,
        VersionFromVersionUpdater $versionFromVersionUpdater
    ) {
        $this->instanceRegistry = $instanceRegistry;
        $this->versionFromVersionUpdater = $versionFromVersionUpdater;
    }

    public function branch(string $entityId, string $entityName, Context $context): Context
    {
        $repository = $this->instanceRegistry
            ->getRepository($entityName);

        $versionId = $repository
            ->createVersion($entityId, $context);

        return $context->createWithVersionId($versionId);
    }

    public function duplicate(string $entityId, string $entityName, Context $versionContext): Context
    {
        return $this->branch($entityId, $entityName, $versionContext);
    }

    public function merge(string $versionId, string $entityName, Context $context): void
    {
        $repository = $this->instanceRegistry
            ->getRepository($entityName);

        $repository->merge($versionId, $context);
    }

    public function releaseAsNew(string $entityId, string $entityName, Context $versionContext): string
    {
        $newEntityId = $this->createNewLiveVersion($entityId, $entityName, $versionContext);
        $this->discard($entityId, $entityName, $versionContext);

        return $newEntityId;
    }

    public function discard(string $entityId, string $entityName, Context $versionContext): void
    {
        $this->instanceRegistry->getRepository($entityName)
            ->delete([['id' => $entityId]], $versionContext);
    }

    public function updateFromLiveVersion(string $entityId, string $entityName, Context $versionContext): string
    {
        $liveContext = $versionContext
            ->createWithVersionId(Defaults::LIVE_VERSION);

        $newVersionId = $this
            ->branch($entityId, $entityName, $liveContext)
            ->getVersionId();

        $newVersionContext = $versionContext
            ->createWithVersionId($newVersionId);

        $this->versionFromVersionUpdater
            ->updateFromVersion($versionContext->getVersionId(), WriteContext::createFromContext($newVersionContext));

        return $newVersionId;
    }

    private function createNewLiveVersion(string $entityId, string $entityName, Context $versionContext): string
    {
        $repository = $this->instanceRegistry
            ->getRepository($entityName);

        $criteria = new Criteria([$entityId]);
        $this->addCriteriaAssociations($repository->getDefinition(), $criteria);

        /** @var CmsPageEntity|null $targetEntity */
        $targetEntity = $repository
            ->search($criteria, $versionContext)
            ->first();

        if (!$targetEntity) {
            throw new NotFoundException('Entity with given id and version could not be found');
        }

        $newEntityId = Uuid::randomHex();
        $data = $this->createNewSerializedEntityArray($newEntityId, $targetEntity);

        $liveContext = $versionContext
            ->createWithVersionId(Defaults::LIVE_VERSION);

        $repository->create([$data], $liveContext);

        return $newEntityId;
    }

    private function createNewSerializedEntityArray(string $newEntityId, CmsPageEntity $entity): array
    {
        $data = $this->serializeData($entity->jsonSerialize());
        $data['id'] = $newEntityId;

        return $data;
    }

    private function serializeData(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($this->isAllowedToUnset($key, $value)) {
                unset($data[$key]);

                continue;
            }

            if ($value instanceof Struct) {
                $actual = $this->serializeData($value->jsonSerialize());
            } elseif (\is_array($value)) {
                $actual = $this->serializeData($value);
            } else {
                $actual = $value;
            }

            $result[$key] = $actual;
        }

        return $result;
    }

    /**
     * @param string|int $key
     * @param mixed $value
     */
    private function isAllowedToUnset($key, $value): bool
    {
        return ($value === null && $key !== 'value')
            || \in_array($key, self::CMS_IDENTIFIER_KEYS, true);
    }

    private function addCriteriaAssociations(
        EntityDefinition $definition,
        Criteria $criteria
    ): void {
        $cascades = $definition->getFields()->filter(static function (Field $field) {
            /** @var CascadeDelete|null $flag */
            $flag = $field->getFlag(CascadeDelete::class);

            return $flag && $flag->isCloneRelevant();
        });

        /** @var AssociationField|FkField $cascade */
        foreach ($cascades as $cascade) {
            $nested = $criteria->getAssociation($cascade->getPropertyName());
            $reference = $cascade->getReferenceDefinition();

            $this->addCriteriaAssociations($reference, $nested);
        }
    }
}

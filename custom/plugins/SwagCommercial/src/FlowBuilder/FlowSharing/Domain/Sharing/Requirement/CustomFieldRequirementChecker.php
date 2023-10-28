<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Content\Flow\Dispatching\Action\SetCustomerCustomFieldAction;
use Shopware\Core\Content\Flow\Dispatching\Action\SetCustomerGroupCustomFieldAction;
use Shopware\Core\Content\Flow\Dispatching\Action\SetOrderCustomFieldAction;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\CustomField\Aggregate\CustomFieldSet\CustomFieldSetDefinition;
use Shopware\Core\System\CustomField\CustomFieldDefinition;

/**
 * @internal
 */
#[Package('business-ops')]
final class CustomFieldRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct
    {
        $flowData = $data->getFlow();

        if (!isset($flowData['sequences'])) {
            return $data;
        }

        /** @var array<int, array<string, mixed>> $sequences */
        $sequences = $flowData['sequences'];

        $customFieldIds = [];
        $customFieldSetIds = [];

        foreach ($sequences as $sequence) {
            if (!\in_array($sequence['actionName'], $this->getCustomFieldActions(), true)) {
                continue;
            }

            /** @var array<string, string>  $config */
            $config = $sequence['config'];

            if (\array_key_exists('customFieldId', $config)) {
                $customFieldIds[] = $config['customFieldId'];
            }

            if (\array_key_exists('customFieldSetId', $config)) {
                $customFieldSetIds[] = $config['customFieldSetId'];
            }
        }

        if (!empty($customFieldIds)) {
            $data->addReference(
                CustomFieldDefinition::ENTITY_NAME,
                $this->getData(CustomFieldDefinition::ENTITY_NAME, $customFieldIds)
            );
        }

        if (!empty($customFieldSetIds)) {
            $data->addReference(
                CustomFieldSetDefinition::ENTITY_NAME,
                $this->getData(CustomFieldSetDefinition::ENTITY_NAME, $customFieldSetIds)
            );
        }

        return $data;
    }

    /**
     * @return array<string>
     */
    private function getCustomFieldActions(): array
    {
        return [
            SetCustomerCustomFieldAction::getName(),
            SetOrderCustomFieldAction::getName(),
            SetCustomerGroupCustomFieldAction::getName(),
        ];
    }

    /**
     * @param array<string> $ids
     *
     * @return array<string, array<string, mixed>>
     */
    private function getData(string $entityName, array $ids): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'LOWER(HEX(`id`)) as `key`',
            'LOWER(HEX(`id`)) as `id`',
            'name',
        ]);
        $query->from($entityName);
        $query->where('`id` IN (:ids)');
        $query->setParameter('ids', Uuid::fromHexToBytesList($ids), ArrayParameterType::STRING);

        /** @var array<string, array<string, string>> $data */
        $data = $query->executeQuery()->fetchAllAssociativeIndexed();

        return $data;
    }
}

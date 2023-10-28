<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use Shopware\Core\Content\Flow\Dispatching\Action\ChangeCustomerGroupAction;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('business-ops')]
final class CustomerGroupRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public function __construct(private Connection $connection)
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

        $customerGroupIds = [];

        foreach ($sequences as $sequence) {
            if ($sequence['actionName'] !== ChangeCustomerGroupAction::getName()) {
                continue;
            }

            /** @var array<string, string>  $config */
            $config = $sequence['config'];

            if (\array_key_exists('customerGroupId', $config)) {
                $customerGroupIds[] = $config['customerGroupId'];
            }
        }

        if (!empty($customerGroupIds)) {
            $data->addReference(CustomerGroupDefinition::ENTITY_NAME, $this->getCustomerGroups($customerGroupIds));
        }

        return $data;
    }

    /**
     * @param array<string> $customerGroupIds
     *
     * @return array<string, array<string, mixed>>
     */
    private function getCustomerGroups(array $customerGroupIds): array
    {
        $data = $this->connection->fetchAllAssociative(
            '
                SELECT LOWER(HEX(`customer_group`.`id`)) as `array_key`,
                    LOWER(HEX(`customer_group`.`id`)) as `id`,
                    `customer_group_translation`.`name`,
                    `locale`.`code` as `locale`
                FROM `customer_group`
                LEFT JOIN `customer_group_translation` ON `customer_group`.`id` = `customer_group_translation`.`customer_group_id`
                LEFT JOIN `language` ON `language`.`id` = `customer_group_translation`.`language_id`
                LEFT JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
                WHERE `customer_group`.`id` IN (:ids)
            ',
            ['ids' => Uuid::fromHexToBytesList($customerGroupIds)],
            ['ids' => ArrayParameterType::STRING]
        );

        return FetchModeHelper::group($data);
    }
}

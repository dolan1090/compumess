<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('business-ops')]
final class AppInstalledRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public const VALIDATOR_NAME = 'appInstalled';

    public function __construct(private readonly Connection $connection)
    {
    }

    public function checkRequirement(array $requirements, Context $context): array
    {
        if (!\array_key_exists(self::VALIDATOR_NAME, $requirements)) {
            return [];
        }

        /** @var array<int, array<string, string>> $requiredApps */
        $requiredApps = $requirements[self::VALIDATOR_NAME];

        $allInstalledApps = $this->getApps();

        $missingApps = [];

        foreach ($requiredApps as $requiredApp) {
            if (isset($allInstalledApps[$requiredApp['name']])) {
                continue;
            }

            $missingApps[] = $requiredApp['name'];
        }

        if (empty($missingApps)) {
            return [];
        }

        return [self::VALIDATOR_NAME => $missingApps];
    }

    public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct
    {
        $flowData = $data->getFlow();

        /** @var string $flowId */
        $flowId = $flowData['id'];

        $appInstalled = array_values($this->getApps($flowId));

        if (!empty($appInstalled)) {
            $data->addRequirement([self::VALIDATOR_NAME => $appInstalled]);
        }

        return $data;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getApps(?string $flowId = null): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'app.name as `key`',
            'LOWER(HEX(app.id)) as `id`',
            'app.name',
            'app.version',
        ]);
        $query->from('app');

        if ($flowId) {
            $query->innerJoin('app', 'app_flow_action', 'app_flow_action', 'app.id = app_flow_action.app_id');
            $query->innerJoin('app_flow_action', 'flow_sequence', 'flow_sequence', 'app_flow_action.id = flow_sequence.app_flow_action_id');
            $query->where('flow_sequence.flow_id = :id');
            $query->setParameter('id', Uuid::fromHexToBytes($flowId));
        }

        /** @var array<string, array<string, string>> $apps */
        $apps = $query->executeQuery()->fetchAllAssociativeIndexed();

        return $apps;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Kernel;

/**
 * @internal
 */
#[Package('business-ops')]
final class PluginInstalledRequirementChecker extends AbstractFlowSharingRequirementChecker
{
    public const VALIDATOR_NAME = 'pluginInstalled';

    /**
     * @param FlowAction[]|iterable $actions
     */
    public function __construct(
        private Kernel $kernel,
        private iterable $actions,
        private Connection $connection
    ) {
    }

    public function checkRequirement(array $requirements, Context $context): array
    {
        if (!\array_key_exists(self::VALIDATOR_NAME, $requirements)) {
            return [];
        }

        /** @var array<int, array<string, string>> $requiredPlugins */
        $requiredPlugins = $requirements[self::VALIDATOR_NAME];

        $installedPlugins = $this->getPlugins();

        $missingPlugins = [];

        foreach ($requiredPlugins as $requiredPlugin) {
            if (isset($installedPlugins[$requiredPlugin['name']])) {
                continue;
            }

            $missingPlugins[] = $requiredPlugin['name'];
        }

        if (empty($missingPlugins)) {
            return [];
        }

        return [self::VALIDATOR_NAME => $missingPlugins];
    }

    public function collect(FlowSharingStruct $data, Context $context): FlowSharingStruct
    {
        $flowData = $data->getFlow();

        if (!isset($flowData['sequences'])) {
            return $data;
        }

        /** @var array<int, array<string, mixed>> $sequences */
        $sequences = $flowData['sequences'];

        $pluginInstalled = $this->getPlugins();
        $activePlugins = $this->getActivePlugins();

        $pluginInstalled = array_map(function ($plugin) use ($activePlugins) {
            $plugin['namespace'] = $activePlugins[$plugin['name']];

            return $plugin;
        }, $pluginInstalled);

        $actionClasses = $this->getActionClasses();

        $usedPlugin = [];

        foreach ($sequences as $sequence) {
            $actionName = $sequence['actionName'];
            if ($actionName === null || $sequence['appFlowActionId'] !== null) {
                continue;
            }

            /** @var string $sequenceId */
            $sequenceId = $sequence['id'];

            if (!isset($actionClasses[$actionName])) {
                $data = $this->removeSequence($data, $sequenceId);

                continue;
            }

            foreach ($pluginInstalled as $plugin) {
                if (str_contains($actionClasses[$actionName], (string) $plugin['namespace'])) {
                    unset($plugin['namespace']);
                    $usedPlugin[] = $plugin;

                    break;
                }
            }
        }

        if (!empty($usedPlugin)) {
            $data->addRequirement([self::VALIDATOR_NAME => $usedPlugin]);
        }

        return $data;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function getPlugins(): array
    {
        /** @var array<string, array<string, string>> $plugins */
        $plugins = $this->connection->fetchAllAssociativeIndexed('
            SELECT `plugin`.`name` as `key`,
                LOWER(HEX(`plugin`.`id`)) as `id`,
                `plugin`.`name`,
                `plugin`.`version`
            FROM `plugin`
            WHERE `plugin`.`active` = 1
        ');

        return $plugins;
    }

    /**
     * @return array<string, string>
     */
    private function getActivePlugins(): array
    {
        $plugins = $this->kernel->getPluginLoader()->getPluginInstances()->getActives();
        $result = [];

        foreach ($plugins as $plugin) {
            $result[$plugin->getName()] = $plugin->getNamespace();
        }

        return $result;
    }

    /**
     * @return array<string, string>
     */
    private function getActionClasses(): array
    {
        $actions = [];

        foreach ($this->actions as $action) {
            if (!$action instanceof FlowAction) {
                continue;
            }

            $actions[$action::getName()] = $action::class;
        }

        return $actions;
    }

    /**
     * Remove an invalid sequences and a sequences behind it
     */
    private function removeSequence(FlowSharingStruct $data, string $sequenceId, bool $isParent = false): FlowSharingStruct
    {
        $flowData = $data->getFlow();

        /** @var array<int, array<string, mixed>> $sequences */
        $sequences = $flowData['sequences'];

        if (!$isParent) {
            $flowData['sequences'] = array_values(array_filter($sequences, fn (array $sequence) => $sequence['id'] !== $sequenceId));

            $data->setFlow($flowData);

            return $this->removeSequence($data, $sequenceId, true);
        }

        foreach ($sequences as $sequence) {
            if ($sequence['parentId'] === $sequenceId) {
                /** @var string $sequenceId */
                $sequenceId = $sequence['id'];

                $data = $this->removeSequence($data, $sequenceId);
            }
        }

        return $data;
    }
}

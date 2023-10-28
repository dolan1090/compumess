<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Content\Flow\Dispatching\Action\AddCustomerTagAction;
use Shopware\Core\Content\Flow\Dispatching\Action\AddOrderTagAction;
use Shopware\Core\Content\Flow\Dispatching\Action\RemoveCustomerTagAction;
use Shopware\Core\Content\Flow\Dispatching\Action\RemoveOrderTagAction;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Tag\TagDefinition;

/**
 * @internal
 */
#[Package('business-ops')]
final class TagRequirementChecker extends AbstractFlowSharingRequirementChecker
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

        $tagIds = [];

        foreach ($sequences as $sequence) {
            if (!\in_array($sequence['actionName'], $this->getTagActions(), true)) {
                continue;
            }

            /** @var array<string, mixed>  $config */
            $config = $sequence['config'];

            if (\array_key_exists('tagIds', $config)) {
                /** @var array<string, string> $tagConfig */
                $tagConfig = $config['tagIds'];

                /** @var array<string> $tagIds */
                $tagIds = array_merge($tagIds, array_keys($tagConfig));
            }
        }

        if (!empty($tagIds)) {
            $data->addReference(TagDefinition::ENTITY_NAME, $this->getTags($tagIds));
        }

        return $data;
    }

    /**
     * @return array<string>
     */
    private function getTagActions(): array
    {
        return [
            AddOrderTagAction::getName(),
            AddCustomerTagAction::getName(),
            RemoveOrderTagAction::getName(),
            RemoveCustomerTagAction::getName(),
        ];
    }

    /**
     * @param array<string> $tagIds
     *
     * @return array<string, array<string, mixed>>
     */
    private function getTags(array $tagIds): array
    {
        /** @var array<string, array<string, mixed>> $tags */
        $tags = $this->connection->fetchAllAssociativeIndexed(
            '
                SELECT LOWER(HEX(`id`)) as `key`, LOWER(HEX(`id`)) as `id`, `name`
                FROM `tag`
                WHERE `id` IN (:tagIds)
            ',
            ['tagIds' => Uuid::fromHexToBytesList($tagIds)],
            ['tagIds' => ArrayParameterType::STRING]
        );

        return $tags;
    }
}

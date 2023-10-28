<?php declare(strict_types=1);

namespace Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\Requirement;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Commercial\FlowBuilder\FlowSharing\Domain\Sharing\FlowSharingStruct;
use Shopware\Core\Content\Flow\Dispatching\Action\SendMailAction;
use Shopware\Core\Content\MailTemplate\MailTemplateDefinition;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('business-ops')]
final class EmailTemplateRequirementChecker extends AbstractFlowSharingRequirementChecker
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

        $mailTemplateIds = [];

        foreach ($sequences as $sequence) {
            if ($sequence['actionName'] !== SendMailAction::getName()) {
                continue;
            }

            /** @var array<string, string>  $config */
            $config = $sequence['config'];

            if (\array_key_exists('mailTemplateId', $config)) {
                $mailTemplateIds[] = $config['mailTemplateId'];
            }
        }

        if (!empty($mailTemplateIds)) {
            /** @var array<string, array<string, array<string, mixed>>> $mailTemplates */
            $mailTemplates = FetchModeHelper::group($this->getMailTemplates($mailTemplateIds));

            $data->addData(MailTemplateDefinition::ENTITY_NAME, $mailTemplates);
        }

        return $data;
    }

    /**
     * @param array<string> $mailTemplateIds
     *
     * @return array<array<string, mixed>>
     */
    private function getMailTemplates(array $mailTemplateIds): array
    {
        return $this->connection->fetchAllAssociative(
            '
                SELECT LOWER(HEX(`mail_template`.`id`)) as `array_key`,
                    LOWER(HEX(`mail_template`.`id`)) as `id`,
                    LOWER(HEX(`mail_template_type`.`id`)) as `mailTemplateTypeId`,
                    `mail_template_type_translation`.`name` as `mailTemplateTypeName`,
                    `locale`.`code` as `locale`,
                    `mail_template`.`system_default` as `systemDefault`,
                    `mail_template_type`.`technical_name` as `technicalName`,
                    `mail_template_translation`.`sender_name` as `senderName`,
                    `mail_template_translation`.`subject`,
                    `mail_template_translation`.`description`,
                    `mail_template_translation`.`content_html` as `contentHtml`,
                    `mail_template_translation`.`content_plain` as `contentPlain`,
                    `mail_template_translation`.`custom_fields` as `customFields`
                FROM `mail_template`
                LEFT JOIN `mail_template_type` ON `mail_template`.`mail_template_type_id` = `mail_template_type`.`id`
                LEFT JOIN `mail_template_translation` ON `mail_template`.`id` = `mail_template_translation`.`mail_template_id`
                LEFT JOIN `language` ON `language`.`id` = `mail_template_translation`.`language_id`
                LEFT JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
                LEFT JOIN `mail_template_type_translation` ON `mail_template_type`.`id` = `mail_template_type_translation`.`mail_template_type_id`
                    AND `language`.`id` = `mail_template_type_translation`.`language_id`
                WHERE `mail_template`.`id` IN (:ids)
            ',
            ['ids' => Uuid::fromHexToBytesList($mailTemplateIds)],
            ['ids' => ArrayParameterType::STRING]
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\ReturnManagement\Domain\Returning\MailTemplateTypes;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\Translations;

/**
 *  @phpstan-type TemplateTypeItem array{id: string, type: string, name: string, nameDe: string, availableEntities: array<string, string|null>}
 *  @phpstan-type TemplateItem array{folder:string, sender: string, senderDe: string, subject: string, subjectDe: string, description: string, descriptionDe: string}
 *
 * @internal
 */
#[Package('checkout')]
class Migration1679202491ReturnManagement_AddMailTemplatesForReturnCreatedAndReturnStatesChanged extends MigrationStep
{
    use ImportTranslationsTrait;

    public function getCreationTimestamp(): int
    {
        return 1679202491;
    }

    public function update(Connection $connection): void
    {
        $templateMapping = $this->getTemplatesMapping();
        foreach ($this->getTemplateTypesMapping() as $index => $type) {
            $templateId = $this->insertMailTemplateType($type, $connection);
            if ($templateId === null) {
                continue;
            }
            $this->insertMailTemplate($templateId, $templateMapping[$index], $connection);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @return TemplateTypeItem[]
     */
    private function getTemplateTypesMapping(): array
    {
        return [
            [
                'id' => Uuid::randomBytes(),
                'type' => MailTemplateTypes::MAILTYPE_ORDER_RETURN_CREATED,
                'name' => 'Order return created',
                'nameDe' => 'Retoure erstellt',
                'availableEntities' => ['order' => 'order', 'salesChannel' => 'sales_channel'],
            ], [
                'id' => Uuid::randomBytes(),
                'type' => MailTemplateTypes::MAILTYPE_ORDER_RETURN_CREATED_MERCHANT,
                'name' => 'Order return created - Administration',
                'nameDe' => 'Retoure erstellt in Administration',
                'availableEntities' => ['order' => 'order', 'salesChannel' => 'sales_channel'],
            ], [
                'id' => Uuid::randomBytes(),
                'type' => MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_IN_PROGRESS,
                'name' => 'Enter order return state: In progress',
                'nameDe' => 'Eintritt Status Retoure: In Bearbeitung',
                'availableEntities' => [
                    'order' => 'order',
                    'salesChannel' => 'sales_channel',
                ],
            ], [
                'id' => Uuid::randomBytes(),
                'type' => MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_COMPLETED,
                'name' => 'Enter order return state: Done',
                'nameDe' => 'Eintritt Status Retoure: Erledigt',
                'availableEntities' => [
                    'order' => 'order',
                    'salesChannel' => 'sales_channel',
                ],
            ], [
                'id' => Uuid::randomBytes(),
                'type' => MailTemplateTypes::MAILTYPE_STATE_ENTER_ORDER_RETURN_STATE_CANCELLED,
                'name' => 'Enter order return state: Cancelled',
                'nameDe' => 'Eintritt Status Retoure: Abgebrochen',
                'availableEntities' => [
                    'order' => 'order',
                    'salesChannel' => 'sales_channel',
                ],
            ],
        ];
    }

    /**
     * @return TemplateItem[]
     */
    private function getTemplatesMapping(): array
    {
        return [
            [
                'folder' => 'return_created_mail',
                'sender' => '{{ salesChannel.name }}',
                'senderDe' => '{{ salesChannel.name }}',
                'subject' => 'Your return with {{ salesChannel.name }} was created',
                'description' => 'Return management Basis Template',
                'descriptionDe' => 'Retourenmanagement Basis Template',
                'subjectDe' => 'Ihre Rücksungung bei {{ salesChannel.name }} wurde erfasst',
            ], [
                'folder' => 'return_created_merchant_mail',
                'sender' => '{{ salesChannel.name }}',
                'senderDe' => '{{ salesChannel.name }}',
                'subject' => 'Customer in {{ salesChannel.name }} created a return with order {{ order.orderNumber }}',
                'subjectDe' => 'Kunde in {{ salesChannel.name }} hat eine Rückgabe mit Bestellung {{ order.orderNumber }} erstellt',
                'description' => 'Return management Basis Template',
                'descriptionDe' => 'Retourenmanagement Basis Template',
            ], [
                'folder' => 'return_state_changed_mail',
                'sender' => '{{ salesChannel.name }}',
                'senderDe' => '{{ salesChannel.name }}',
                'subject' => 'Your return with {{ salesChannel.name }} is in process',
                'subjectDe' => 'Rücksendung bei {{ salesChannel.name }} ist in Bearbeitung',
                'description' => 'Return management Basis Template',
                'descriptionDe' => 'Retourenmanagement Basis Template',
            ], [
                'folder' => 'return_state_changed_mail',
                'sender' => '{{ salesChannel.name }}',
                'senderDe' => '{{ salesChannel.name }}',
                'subject' => 'Your return with {{ salesChannel.name }} is completed',
                'subjectDe' => 'Rücksendung bei {{ salesChannel.name }} ist komplett abgeschlossen',
                'description' => 'Return management Basis Template',
                'descriptionDe' => 'Retourenmanagement Basis Template',
            ], [
                'folder' => 'return_state_changed_mail',
                'sender' => '{{ salesChannel.name }}',
                'senderDe' => '{{ salesChannel.name }}',
                'subject' => 'Your return with {{ salesChannel.name }} is cancelled',
                'subjectDe' => 'Rücksendung bei {{ salesChannel.name }} ist Abgebrochen',
                'description' => 'Return management Basis Template',
                'descriptionDe' => 'Retourenmanagement Basis Template',
            ],
        ];
    }

    /**
     * @param TemplateTypeItem $templateTypeItem
     */
    private function insertMailTemplateType($templateTypeItem, Connection $connection): ?string
    {
        $technicalName = $templateTypeItem['type'];
        $isExistingMailTemplate = $this->mailTemplatesExist($technicalName, $connection);
        if ($isExistingMailTemplate) {
            return null;
        }

        $typeId = $templateTypeItem['id'];
        $nameEn = $templateTypeItem['name'];
        $nameDe = $templateTypeItem['nameDe'];

        $existingTypeId = $this->getExistingMailTemplateTypeId($technicalName, $connection);
        if ($existingTypeId !== null) {
            $typeId = $existingTypeId;
        } else {
            $connection->insert(
                'mail_template_type',
                [
                    'id' => $typeId,
                    'technical_name' => $technicalName,
                    'available_entities' => json_encode($templateTypeItem['availableEntities']),
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $translations = new Translations(
                [
                    'mail_template_type_id' => $typeId,
                    'name' => $nameDe,
                ],
                [
                    'mail_template_type_id' => $typeId,
                    'name' => $nameEn,
                ]
            );

            $this->importTranslation('mail_template_type_translation', $translations, $connection);
        }

        $templateId = Uuid::randomBytes();

        $connection->insert(
            'mail_template',
            [
                'id' => $templateId,
                'mail_template_type_id' => $typeId,
                'system_default' => 1,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        return $templateId;
    }

    /**
     * @param TemplateItem $templateItem
     */
    private function insertMailTemplate(string $templateId, $templateItem, Connection $connection): void
    {
        $mailContentHtmlEn = \file_get_contents(__DIR__ . '/Fixtures/mails/' . $templateItem['folder'] . '/en-html.html.twig');
        $mailContentPlainEn = \file_get_contents(__DIR__ . '/Fixtures/mails/' . $templateItem['folder'] . '/en-plain.html.twig');

        $mailContentHtmlDe = \file_get_contents(__DIR__ . '/Fixtures/mails/' . $templateItem['folder'] . '/de-html.html.twig');
        $mailContentPlainDe = \file_get_contents(__DIR__ . '/Fixtures/mails/' . $templateItem['folder'] . '/de-plain.html.twig');

        $translations = new Translations(
            [
                'mail_template_id' => $templateId,
                'sender_name' => $templateItem['senderDe'],
                'subject' => $templateItem['subjectDe'],
                'description' => $templateItem['descriptionDe'],
                'content_html' => $mailContentHtmlDe,
                'content_plain' => $mailContentPlainDe,
            ],
            [
                'mail_template_id' => $templateId,
                'sender_name' => $templateItem['sender'],
                'subject' => $templateItem['subject'],
                'description' => $templateItem['description'],
                'content_html' => $mailContentHtmlEn,
                'content_plain' => $mailContentPlainEn,
            ],
        );

        $this->importTranslation('mail_template_translation', $translations, $connection);
    }

    private function getExistingMailTemplateTypeId(string $technicalName, Connection $connection): ?string
    {
        /** @var string|null $result */
        $result = $connection->createQueryBuilder()
            ->select('id')
            ->from('mail_template_type')
            ->where('technical_name = :technicalName')
            ->setParameter('technicalName', $technicalName)
            ->executeQuery()
            ->fetchOne();

        if (\is_bool($result)) {
            return null;
        }

        return $result;
    }

    private function mailTemplatesExist(string $technicalName, Connection $connection): bool
    {
        return (bool) $connection->fetchOne(
            'SELECT count(`id`) FROM `mail_template`
             WHERE `mail_template_type_id`
             IN (SELECT `id` FROM `mail_template_type` WHERE `technical_name` = :technical_name)',
            [
                'technical_name' => $technicalName,
            ]
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\MailUpdate;
use Shopware\Core\Migration\Traits\UpdateMailTrait;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1689057417CreateEmployeeStatusChangeMailTemplate extends MigrationStep
{
    use UpdateMailTrait;

    public const TYPE = 'employee.status.changed';

    private const GERMAN_LANGUAGE_NAME = 'Deutsch';

    private const ENGLISH_LANGUAGE_NAME = 'English';

    public function getCreationTimestamp(): int
    {
        return 1689057417;
    }

    public function update(Connection $connection): void
    {
        $mailTemplateTypeId = $this->insertMailTemplateTypeData($connection);
        $this->insertMailTemplateData($mailTemplateTypeId, $connection);
        $this->updateMailTemplateContent($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
        // nth
    }

    private function insertMailTemplateTypeData(Connection $connection): string
    {
        $existingMailTemplateTypeId = $connection->fetchOne('SELECT id FROM mail_template_type WHERE technical_name = :technicalName', ['technicalName' => self::TYPE]);

        if ($existingMailTemplateTypeId) {
            return \is_string($existingMailTemplateTypeId) ? $existingMailTemplateTypeId : '';
        }

        $templateTypeId = Uuid::randomBytes();
        $connection->insert('mail_template_type', [
            'id' => $templateTypeId,
            'technical_name' => self::TYPE,
            'available_entities' => '{"order":"order","salesChannel":"sales_channel"}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $defaultLanguageId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $englishLanguageId = $this->fetchLanguageIdByName(self::ENGLISH_LANGUAGE_NAME, $connection);
        $germanLanguageId = $this->fetchLanguageIdByName(self::GERMAN_LANGUAGE_NAME, $connection);

        if (!\in_array($defaultLanguageId, [$englishLanguageId, $germanLanguageId], true)) {
            $connection->insert(
                'mail_template_type_translation',
                [
                    'mail_template_type_id' => $templateTypeId,
                    'language_id' => $defaultLanguageId,
                    'name' => 'Employee status changed',
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        if ($englishLanguageId) {
            $connection->insert(
                'mail_template_type_translation',
                [
                    'mail_template_type_id' => $templateTypeId,
                    'language_id' => $englishLanguageId,
                    'name' => 'Employee status changed',
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        if ($germanLanguageId) {
            $connection->insert(
                'mail_template_type_translation',
                [
                    'mail_template_type_id' => $templateTypeId,
                    'language_id' => $germanLanguageId,
                    'name' => 'Mitarbeiter-Status geändert',
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        return $templateTypeId;
    }

    private function insertMailTemplateData(string $templateTypeId, Connection $connection): void
    {
        $templateId = Uuid::randomBytes();
        $connection->insert(
            'mail_template',
            [
                'id' => $templateId,
                'mail_template_type_id' => $templateTypeId,
                'system_default' => 1,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $defaultLanguageId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $englishLanguageId = $this->fetchLanguageIdByName(self::ENGLISH_LANGUAGE_NAME, $connection);
        $germanLanguageId = $this->fetchLanguageIdByName(self::GERMAN_LANGUAGE_NAME, $connection);

        if (!\in_array($defaultLanguageId, [$englishLanguageId, $germanLanguageId], true)) {
            $connection->insert(
                'mail_template_translation',
                [
                    'subject' => 'Employee status changed',
                    'description' => 'Shopware Default Template',
                    'sender_name' => '{{ salesChannel.name }}',
                    'content_html' => '',
                    'content_plain' => '',
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'mail_template_id' => $templateId,
                    'language_id' => $defaultLanguageId,
                ]
            );
        }

        if ($englishLanguageId) {
            $connection->insert(
                'mail_template_translation',
                [
                    'subject' => 'Employee status changed',
                    'description' => 'Shopware Default Template',
                    'sender_name' => '{{ salesChannel.name }}',
                    'content_html' => '',
                    'content_plain' => '',
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'mail_template_id' => $templateId,
                    'language_id' => $englishLanguageId,
                ]
            );
        }

        if ($germanLanguageId) {
            $connection->insert(
                'mail_template_translation',
                [
                    'subject' => 'Mitarbeiter-Status geändert',
                    'description' => 'Shopware Basis Template',
                    'sender_name' => '{{ salesChannel.name }}',
                    'content_html' => '',
                    'content_plain' => '',
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    'mail_template_id' => $templateId,
                    'language_id' => $germanLanguageId,
                ]
            );
        }
    }

    private function fetchLanguageIdByName(string $languageName, Connection $connection): ?string
    {
        try {
            $result = $connection->fetchOne(
                'SELECT id FROM `language` WHERE `name` = :languageName',
                ['languageName' => $languageName]
            );

            if (!\is_string($result)) {
                return null;
            }

            return $result;
        } catch (\Throwable) {
            return null;
        }
    }

    private function updateMailTemplateContent(Connection $connection): void
    {
        $update = new MailUpdate(
            self::TYPE,
            (string) file_get_contents(__DIR__ . '/Fixtures/mails/employee.status.changed/en-plain.html.twig'),
            (string) file_get_contents(__DIR__ . '/Fixtures/mails/employee.status.changed/en-html.html.twig'),
            (string) file_get_contents(__DIR__ . '/Fixtures/mails/employee.status.changed/de-plain.html.twig'),
            (string) file_get_contents(__DIR__ . '/Fixtures/mails/employee.status.changed/de-html.html.twig'),
        );

        $this->updateMail($update, $connection);
    }
}
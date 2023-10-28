<?php declare(strict_types=1);

namespace Shopware\Commercial\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\Translations;

/**
 * @internal
 */
#[Package('checkout')]
class Migration1669021037ReturnManagement_PartialCancellationDocumentMailTemplate extends MigrationStep
{
    use ImportTranslationsTrait;

    final public const MAIL_TYPE_DOCUMENT_PARTIAL_CANCELLATION = 'partial_cancellation_mail';

    final public const DOCUMENT_TYPE_NAME_EN = 'Partial cancellation';

    final public const DOCUMENT_TYPE_NAME_DE = 'Teilstornierung';

    public function getCreationTimestamp(): int
    {
        return 1669021037;
    }

    public function update(Connection $connection): void
    {
        $technicalName = self::MAIL_TYPE_DOCUMENT_PARTIAL_CANCELLATION;
        $typeId = Uuid::randomBytes();
        $templateId = Uuid::randomBytes();
        $name = self::DOCUMENT_TYPE_NAME_EN;
        $nameDe = self::DOCUMENT_TYPE_NAME_DE;

        $isExistingMailTemplate = $this->mailTemplatesExist($technicalName, $connection);
        if ($isExistingMailTemplate) {
            return;
        }

        $existingTypeId = $this->getExistingMailTemplateTypeId($technicalName, $connection);
        if ($existingTypeId !== null) {
            $typeId = $existingTypeId;
        } else {
            $connection->insert(
                'mail_template_type',
                [
                    'id' => $typeId,
                    'technical_name' => $technicalName,
                    'available_entities' => json_encode(['order' => 'order', 'salesChannel' => 'sales_channel']),
                    'template_data' => '{"order":{"orderNumber":"10060","orderCustomer":{"firstName":"Max","lastName":"Mustermann"}},"salesChannel":{"name":"Storefront"}}',
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
                    'name' => $name,
                ]
            );

            $this->importTranslation('mail_template_type_translation', $translations, $connection);
        }

        $connection->insert(
            'mail_template',
            [
                'id' => $templateId,
                'mail_template_type_id' => $typeId,
                'system_default' => 1,
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $partialCancellationEnHtml = \file_get_contents(__DIR__ . '/Fixtures/mails/partial_cancellation_mail/en-html.html.twig');
        $partialCancellationEnPlain = \file_get_contents(__DIR__ . '/Fixtures/mails/partial_cancellation_mail/en-plain.html.twig');

        $partialCancellationDeHtml = \file_get_contents(__DIR__ . '/Fixtures/mails/partial_cancellation_mail/de-html.html.twig');
        $partialCancellationDePlain = \file_get_contents(__DIR__ . '/Fixtures/mails/partial_cancellation_mail/de-plain.html.twig');

        $translations = new Translations(
            [
                'mail_template_id' => $templateId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'Neues Dokument für Ihre Bestellung',
                'content_html' => $partialCancellationDeHtml,
                'content_plain' => $partialCancellationDePlain,
            ],
            [
                'mail_template_id' => $templateId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => 'New document for your order',
                'content_html' => $partialCancellationEnHtml,
                'content_plain' => $partialCancellationEnPlain,
            ],
        );

        $this->importTranslation('mail_template_translation', $translations, $connection);
    }

    public function updateDestructive(Connection $connection): void
    {
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

        return $result ?: null;
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

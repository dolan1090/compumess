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
 *
 * @phpstan-type Translation array{de-DE: string, en-GB: string}
 * @phpstan-type MailTemplate array{technicalName: string, type: Translation, subject: Translation, description: Translation, contentPlain: Translation, contentHtml: Translation}
 */
#[Package('checkout')]
class Migration1690961072SubscriptionMailTemplates extends MigrationStep
{
    use ImportTranslationsTrait;

    final public const MAIL_TYPE_SUBSCRIPTION_CONFIRMATION = 'subscription_confirmation';

    final public const MAIL_TYPE_SUBSCRIPTION_CANCELLATION = 'subscription_cancellation';

    final public const MAIL_TYPE_SUBSCRIPTION_REACTIVATION = 'subscription_reactivation';

    final public const MAIL_TYPE_SUBSCRIPTION_PAUSE = 'subscription_pause';

    public function getCreationTimestamp(): int
    {
        return 1690961072;
    }

    public function update(Connection $connection): void
    {
        /** @var MailTemplate $template */
        foreach (self::getTemplates() as $template) {
            $this->updateMailTemplate($template, $connection);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    /**
     * @param MailTemplate $templateData
     */
    private function updateMailTemplate(array $templateData, Connection $connection): void
    {
        $technicalName = $templateData['technicalName'];

        if ($this->mailTemplatesExist($technicalName, $connection)) {
            return;
        }

        $typeId = $this->getExistingMailTemplateTypeId($technicalName, $connection);

        if (!$typeId) {
            $typeId = Uuid::randomBytes();

            $connection->insert(
                'mail_template_type',
                [
                    'id' => $typeId,
                    'technical_name' => $technicalName,
                    'available_entities' => \json_encode(['subscription' => 'subscription', 'salesChannel' => 'sales_channel']),
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $translations = new Translations(
                [
                    'mail_template_type_id' => $typeId,
                    'name' => $templateData['type']['de-DE'],
                ],
                [
                    'mail_template_type_id' => $typeId,
                    'name' => $templateData['type']['en-GB'],
                ],
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

        $translations = new Translations(
            [
                'mail_template_id' => $templateId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => $templateData['subject']['de-DE'],
                'description' => $templateData['description']['de-DE'],
                'content_html' => $templateData['contentHtml']['de-DE'],
                'content_plain' => $templateData['contentPlain']['de-DE'],
            ],
            [
                'mail_template_id' => $templateId,
                'sender_name' => '{{ salesChannel.name }}',
                'subject' => $templateData['subject']['en-GB'],
                'description' => $templateData['description']['en-GB'],
                'content_html' => $templateData['contentHtml']['en-GB'],
                'content_plain' => $templateData['contentPlain']['en-GB'],
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

    /**
     * @return \Generator<MailTemplate>
     */
    private static function getTemplates(): \Generator
    {
        yield [
            'technicalName' => self::MAIL_TYPE_SUBSCRIPTION_CONFIRMATION,
            'subject' => [
                'en-GB' => 'Subscription confirmation',
                'de-DE' => 'Bestätigung Ihres Abonnements',
            ],
            'type' => [
                'en-GB' => 'Subscription confirmation',
                'de-DE' => 'Abonnement-Bestätigung',
            ],
            'description' => [
                'en-GB' => 'Subscription Basis Template',
                'de-DE' => 'Subscription Basis Template',
            ],
            'contentHtml' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CONFIRMATION . '/en-html.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CONFIRMATION . '/de-html.html.twig'),
            ],
            'contentPlain' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CONFIRMATION . '/en-plain.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CONFIRMATION . '/de-plain.html.twig'),
            ],
        ];

        yield [
            'technicalName' => self::MAIL_TYPE_SUBSCRIPTION_CANCELLATION,
            'subject' => [
                'en-GB' => 'Subscription cancellation',
                'de-DE' => 'Kündigung Ihres Abonnements',
            ],
            'type' => [
                'en-GB' => 'Subscription cancellation',
                'de-DE' => 'Abonnement-Kündigung',
            ],
            'description' => [
                'en-GB' => 'Subscription Basis Template',
                'de-DE' => 'Subscription Basis Template',
            ],
            'contentHtml' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CANCELLATION . '/en-html.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CANCELLATION . '/de-html.html.twig'),
            ],
            'contentPlain' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CANCELLATION . '/en-plain.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_CANCELLATION . '/de-plain.html.twig'),
            ],
        ];

        yield [
            'technicalName' => self::MAIL_TYPE_SUBSCRIPTION_PAUSE,
            'subject' => [
                'en-GB' => 'Subscription pause',
                'de-DE' => 'Pausierung Ihres Abonnements',
            ],
            'type' => [
                'en-GB' => 'Subscription pause',
                'de-DE' => 'Abonnement-Pausierung',
            ],
            'description' => [
                'en-GB' => 'Subscription Basis Template',
                'de-DE' => 'Subscription Basis Template',
            ],
            'contentHtml' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_PAUSE . '/en-html.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_PAUSE . '/de-html.html.twig'),
            ],
            'contentPlain' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_PAUSE . '/en-plain.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_PAUSE . '/de-plain.html.twig'),
            ],
        ];

        yield [
            'technicalName' => self::MAIL_TYPE_SUBSCRIPTION_REACTIVATION,
            'subject' => [
                'en-GB' => 'Subscription reactivation',
                'de-DE' => 'Reaktivierung Ihres Abonnements',
            ],
            'type' => [
                'en-GB' => 'Subscription reactivation',
                'de-DE' => 'Abonnement-Reaktivierung',
            ],
            'description' => [
                'en-GB' => 'Subscription Basis Template',
                'de-DE' => 'Subscription Basis Template',
            ],
            'contentHtml' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_REACTIVATION . '/en-html.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_REACTIVATION . '/de-html.html.twig'),
            ],
            'contentPlain' => [
                'en-GB' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_REACTIVATION . '/en-plain.html.twig'),
                'de-DE' => (string) \file_get_contents(__DIR__ . '/Fixtures/mails/' . self::MAIL_TYPE_SUBSCRIPTION_REACTIVATION . '/de-plain.html.twig'),
            ],
        ];
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Subscriber;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Event\StorefrontRenderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('inventory')]
class StorefrontRenderEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorefrontRenderEvent::class => 'onRenderReview',
        ];
    }

    public function onRenderReview(StorefrontRenderEvent $event): void
    {
        if (!License::get('REVIEW_TRANSLATOR-1649854')) {
            return;
        }

        if ($event->getView() === 'storefront/page/product-detail/review/review.html.twig'
            || $event->getView() === '@Storefront/storefront/page/product-detail/index.html.twig'
            || $event->getView() === '@Storefront/storefront/page/content/product-detail.html.twig'
            || $event->getView() === '@Storefront/storefront/component/review/review.html.twig') {
            $translation = $this->getLanguages($event->getContext()->getLanguageId());

            if (!$translation) {
                return;
            }

            $event->setParameter(
                'translateTo',
                $translation
            );
        }
    }

    private function getLanguages(string $activeLanguageId): ?string
    {
        $sql = <<<'SQL'
            SELECT IFNULL(locale_translation.name, fallback_translation.name) AS language_name
            FROM language
                LEFT JOIN locale_translation ON locale_translation.locale_id = language.locale_id
                    AND locale_translation.language_id = :languageId
                LEFT JOIN locale_translation fallback_translation ON fallback_translation.locale_id = language.locale_id
                    AND fallback_translation.language_id = :fallbackLanguageId
            WHERE language.id = :languageId
        SQL;

        /** @var string|null $translation */
        $translation = $this->connection->fetchOne($sql, [
            'languageId' => Uuid::fromHexToBytes($activeLanguageId),
            'fallbackLanguageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
        ]);

        return $translation;
    }
}

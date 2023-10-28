<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Indexing;

use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\Indexing\Event\ElasticsearchIndexConfigEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class ElasticsearchIndexConfigSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     *
     * @param array<mixed> $advancedSearchConfig
     */
    public function __construct(private readonly array $advancedSearchConfig)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ElasticsearchIndexConfigEvent::class => 'beforeIndexConfigCreated',
        ];
    }

    public function beforeIndexConfigCreated(ElasticsearchIndexConfigEvent $event): void
    {
        if (!Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        $config = $event->getConfig();

        $event->setConfig(array_replace_recursive($config, $this->advancedSearchConfig));
    }
}

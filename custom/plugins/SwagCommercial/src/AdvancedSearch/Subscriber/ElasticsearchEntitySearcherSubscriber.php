<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Subscriber;

use OpenSearchDSL\Query\Compound\BoolQuery;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\BoostingQueryStruct;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Elasticsearch\Framework\DataAbstractionLayer\Event\ElasticsearchEntitySearcherSearchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class ElasticsearchEntitySearcherSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ElasticsearchEntitySearcherSearchEvent::class => 'applyBoosting',
        ];
    }

    public function applyBoosting(ElasticsearchEntitySearcherSearchEvent $event): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        $criteria = $event->getCriteria();
        if (!$criteria->hasExtension(BoostingQueryStruct::CRITERIA_EXTENSION) || !$criteria->getExtension(BoostingQueryStruct::CRITERIA_EXTENSION) instanceof BoostingQueryStruct) {
            return;
        }

        $boostingQueryStruct = $criteria->getExtension(BoostingQueryStruct::CRITERIA_EXTENSION);
        $event->getSearch()->addQuery($boostingQueryStruct->getQuery(), BoolQuery::SHOULD);
    }
}

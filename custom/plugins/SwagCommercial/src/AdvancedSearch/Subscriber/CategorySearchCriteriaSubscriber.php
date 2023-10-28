<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Subscriber;

use Shopware\Commercial\AdvancedSearch\Event\MultiContentSearchCriteriaEvent;
use Shopware\Commercial\AdvancedSearch\Event\MultiContentSuggestCriteriaEvent;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('buyers-experience')]
class CategorySearchCriteriaSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MultiContentSearchCriteriaEvent::class => 'onSearchCriteria',
            MultiContentSuggestCriteriaEvent::class => 'onSuggestCriteria',
        ];
    }

    public function onSearchCriteria(MultiContentSearchCriteriaEvent $event): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        if ($event->getDefinition()->getEntityName() !== CategoryDefinition::ENTITY_NAME) {
            return;
        }

        $this->processCriteria($event->getCriteria(), $event->getContext());
    }

    public function onSuggestCriteria(MultiContentSuggestCriteriaEvent $event): void
    {
        if (!License::get('ADVANCED_SEARCH-3068620') || !Feature::isActive('ES_MULTILINGUAL_INDEX')) {
            return;
        }

        if ($event->getDefinition()->getEntityName() !== CategoryDefinition::ENTITY_NAME) {
            return;
        }

        $this->processCriteria($event->getCriteria(), $event->getContext());
    }

    private function processCriteria(Criteria $criteria, SalesChannelContext $context): Criteria
    {
        $criteria->addFilter(new EqualsFilter('active', true));

        $this->buildCategoryFilters($criteria, $context->getSalesChannel());

        return $criteria;
    }

    private function buildCategoryFilters(Criteria $criteria, SalesChannelEntity $salesChannel): void
    {
        $ids = array_filter([
            $salesChannel->getNavigationCategoryId(),
            $salesChannel->getServiceCategoryId(),
            $salesChannel->getFooterCategoryId(),
        ]);

        if (empty($ids)) {
            return;
        }

        $criteria->addFilter(new OrFilter(array_map(static fn (string $id) => new ContainsFilter('path', '|' . $id . '|'), $ids)));
    }
}

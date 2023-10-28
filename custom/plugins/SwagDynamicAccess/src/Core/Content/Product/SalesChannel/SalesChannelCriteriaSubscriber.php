<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Core\Content\Product\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\System\SalesChannel\Event\SalesChannelProcessCriteriaEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelCriteriaSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.product.process.criteria' => 'addProductRuleFilter',
            'sales_channel.category.process.criteria' => 'addCategoryRuleFilter',
            'sales_channel.landing_page.process.criteria' => 'addLandingPageRuleFilter',
        ];
    }

    public function addProductRuleFilter(SalesChannelProcessCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        if (!$this->hasFilter($criteria, ProductRuleFilter::class)) {
            $criteria->addFilter(new ProductRuleFilter($event->getSalesChannelContext()->getRuleIds()));
        }
    }

    public function addCategoryRuleFilter(SalesChannelProcessCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        if (!$this->hasFilter($criteria, CategoryRuleFilter::class)) {
            $criteria->addFilter(new CategoryRuleFilter($event->getSalesChannelContext()->getRuleIds()));
        }
    }

    public function addLandingPageRuleFilter(SalesChannelProcessCriteriaEvent $event): void
    {
        $criteria = $event->getCriteria();

        if (!$this->hasFilter($criteria, LandingPageRuleFilter::class)) {
            $criteria->addFilter(new LandingPageRuleFilter($event->getSalesChannelContext()->getRuleIds()));
        }
    }

    /**
     * @param class-string<Filter> $filterClassName
     */
    private function hasFilter(Criteria $criteria, string $filterClassName): bool
    {
        foreach ($criteria->getFilters() as $filter) {
            if ($filter instanceof $filterClassName) {
                return true;
            }
        }

        return false;
    }
}

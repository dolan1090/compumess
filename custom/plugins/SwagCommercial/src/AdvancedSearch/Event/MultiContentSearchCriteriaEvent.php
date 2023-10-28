<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Event;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @final
 *
 * @codeCoverageIgnore
 */
#[Package('buyers-experience')]
class MultiContentSearchCriteriaEvent extends Event
{
    public function __construct(
        private readonly EntityDefinition $definition,
        private readonly Criteria $criteria,
        private readonly SalesChannelContext $context
    ) {
    }

    public function getDefinition(): EntityDefinition
    {
        return $this->definition;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function getContext(): SalesChannelContext
    {
        return $this->context;
    }
}

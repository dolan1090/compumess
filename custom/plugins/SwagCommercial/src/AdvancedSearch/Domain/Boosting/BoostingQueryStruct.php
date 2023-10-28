<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting;

use OpenSearchDSL\BuilderInterface;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('buyers-experience')]
class BoostingQueryStruct extends Struct
{
    final public const CRITERIA_EXTENSION = 'es-boosting';

    public function __construct(
        private readonly BuilderInterface $query
    ) {
    }

    public function getQuery(): BuilderInterface
    {
        return $this->query;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolver;

use Shopware\Commercial\AdvancedSearch\Domain\Boosting\Boosting;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
abstract class AbstractEntityStreamResolver
{
    abstract public function getType(): string;

    abstract public function supports(string $entityName): bool;

    /**
     * @param array<array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null}> $boostings
     *
     * @return array<Boosting>
     */
    abstract public function resolve(array $boostings): array;
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolver;

use Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolverException;
use Shopware\Core\Framework\Log\Package;

#[Package('buyers-experience')]
class EntityStreamResolverRegistry
{
    /**
     * @param iterable<AbstractEntityStreamResolver> $streamResolver
     *
     * @internal
     */
    public function __construct(private readonly iterable $streamResolver)
    {
    }

    public function getResolver(string $entityName): AbstractEntityStreamResolver
    {
        foreach ($this->streamResolver as $resolver) {
            if (!$resolver->supports($entityName)) {
                continue;
            }

            return $resolver;
        }

        throw StreamResolverException::resolverNotFound($entityName);
    }
}

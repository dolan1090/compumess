<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('buyers-experience')]
class StreamResolverException extends HttpException
{
    final public const RESOLVER_DOES_NOT_FOUND_EXCEPTION = 'ADVANCED_SEARCH__RESOLVER_DOES_NOT_FOUND';

    public static function resolverNotFound(string $entity): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::RESOLVER_DOES_NOT_FOUND_EXCEPTION,
            'The resolver for entity {{ entity }} does not exists.',
            ['entity' => $entity]
        );
    }
}

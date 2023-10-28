<?php declare(strict_types=1);

namespace Shopware\Commercial\Test\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @internal
 *
 * @Annotation
 *
 * @Target({"METHOD", "CLASS"})
 */
final class ActiveFeatureToggles
{
    /**
     * @var array<string, int|string>
     */
    public array $toggles = [];
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing;

use Shopware\Core\Framework\Log\Package;

#[Package('merchant-services')]
class Feature
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly string $description,
        public readonly ?string $type = null
    ) {
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Domain\Product\Review;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

/**
 * @final
 *
 * @internal
 */
#[Package('inventory')]
class GenerationContext extends Struct
{
    /**
     * @param array<mixed, mixed> $locales
     * @param array<mixed, mixed> $reviews
     * @param array<string|int, string> $languageIds
     *
     * @internal
     */
    public function __construct(
        public readonly string $productId,
        public readonly array $locales,
        public readonly array $reviews,
        public readonly string $salesChannelId,
        public readonly int $length = 300,
        public readonly ?string $mood = null,
        public array $languageIds = [],
        public readonly bool $allowOverwrite = true,
    ) {
    }

    /**
     * @param array<string|int, string> $languageIds
     */
    public function setLanguageIds(array $languageIds): void
    {
        $this->languageIds = $languageIds;
    }
}

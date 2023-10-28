<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Exception;

use Shopware\Core\Framework\Log\Package;

#[Package('merchant-services')]
class InvalidFeaturesException extends \RuntimeException
{
    /**
     * @param array<string> $features
     */
    public static function fromFeatures(array $features): self
    {
        return new self(
            sprintf('The following are not supported features: %s', implode(', ', $features))
        );
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\Domain\CustomerSpecificFeature;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerSpecificFeatureService implements ResetInterface
{
    /**
     * @var array<string, array<string, bool>>
     */
    private array $specificFeatures = [];

    public function __construct(private readonly Connection $connection)
    {
    }

    public function reset(): void
    {
        $this->specificFeatures = [];
    }

    public function isAllowed(?string $customerId, string $feature): bool
    {
        if (!$customerId) {
            return false;
        }

        if (isset($this->specificFeatures[$customerId])) {
            return $this->specificFeatures[$customerId][$feature] ?? false;
        }

        $specificFeatures = $this->connection->fetchOne(
            'SELECT `customer_specific_features`.`features`
            FROM `customer_specific_features`
            WHERE `customer_specific_features`.`customer_id` = :customerId',
            [
                'customerId' => Uuid::fromHexToBytes($customerId),
            ]
        );

        if (!\is_string($specificFeatures)) {
            return false;
        }

        /** @var array<string, bool> $features */
        $features = \json_decode($specificFeatures, true, 512, \JSON_THROW_ON_ERROR);

        $this->specificFeatures[$customerId] = $features;

        return $this->specificFeatures[$customerId][$feature] ?? false;
    }
}

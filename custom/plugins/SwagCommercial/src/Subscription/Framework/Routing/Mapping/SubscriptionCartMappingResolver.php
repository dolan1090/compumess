<?php declare(strict_types=1);

namespace Shopware\Commercial\Subscription\Framework\Routing\Mapping;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\Subscription\Framework\Struct\SubscriptionContextStruct;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

#[Package('checkout')]
class SubscriptionCartMappingResolver
{
    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function persistMapping(SalesChannelContext $salesChannelContext): void
    {
        if (!License::get('SUBSCRIPTIONS-3156213')) {
            return;
        }

        $struct = $salesChannelContext->getExtension(SubscriptionContextStruct::SUBSCRIPTION_EXTENSION);
        if (!$struct instanceof SubscriptionContextStruct) {
            return;
        }

        $stmt = $this->connection->prepare('
            INSERT INTO `subscription_cart` (`subscription_token`, `main_token`, `interval_id`, `plan_id`, `created_at`)
            VALUES (:subscriptionToken, :mainToken, :intervalId, :planId, :now)
            ON DUPLICATE KEY UPDATE `main_token` = :mainToken, `interval_id` = :intervalId, `plan_id` = :planId, `updated_at` = :now
        ');

        $stmt->executeStatement([
            'subscriptionToken' => $struct->getSubscriptionToken(),
            'mainToken' => $struct->getMainToken(),
            'intervalId' => Uuid::fromHexToBytes($struct->getInterval()->getId()),
            'planId' => Uuid::fromHexToBytes($struct->getPlan()->getId()),
            'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    public function replaceSubscriptionToken(string $oldToken, string $newMainToken): void
    {
        $stmt = $this->connection->prepare('
            UPDATE `subscription_cart`
            SET `main_token` = :newMainToken, `updated_at` = :now
            WHERE `subscription_token` = :oldToken
        ');

        $stmt->executeStatement([
            'newMainToken' => $newMainToken,
            'oldToken' => $oldToken,
            'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    /**
     * @return array{subscriptionToken: string, intervalId: string, planId: string}[]
     */
    public function getSubscriptionCarts(string $mainToken): array
    {
        /**
         * @var array{subscriptionToken: string, intervalId: string, planId: string}[] $result
         */
        $result = $this->connection->executeQuery(
            '
                SELECT `subscription_token` as subscriptionToken, LOWER(HEX(`interval_id`)) as intervalId, LOWER(HEX(`plan_id`)) as planId
                FROM subscription_cart WHERE main_token = :mainToken',
            ['mainToken' => $mainToken]
        )->fetchAllAssociative();

        return $result;
    }

    public function getSubscriptionToken(string $intervalId, string $planId, string $mainToken): ?string
    {
        $result = $this->connection->executeQuery(
            'SELECT `subscription_token` FROM subscription_cart WHERE main_token = :mainToken AND interval_id = :intervalId AND plan_id = :planId',
            [
                'mainToken' => $mainToken,
                'intervalId' => Uuid::fromHexToBytes($intervalId),
                'planId' => Uuid::fromHexToBytes($planId),
            ],
        )->fetchOne();

        if (!$result || !\is_string($result)) {
            return null;
        }

        return $result;
    }

    public function deleteSubscriptionCart(string $token): void
    {
        $this->connection->executeStatement('DELETE FROM subscription_cart WHERE `subscription_token` = :token', ['token' => $token]);
    }
}

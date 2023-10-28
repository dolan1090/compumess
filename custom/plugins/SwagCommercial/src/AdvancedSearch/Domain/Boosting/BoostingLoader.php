<?php declare(strict_types=1);

namespace Shopware\Commercial\AdvancedSearch\Domain\Boosting;

use Doctrine\DBAL\Connection;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolver\EntityStreamResolver;
use Shopware\Commercial\AdvancedSearch\Domain\Boosting\StreamResolver\ProductStreamResolver;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * @final
 */
#[Package('buyers-experience')]
class BoostingLoader implements ResetInterface, EventSubscriberInterface
{
    /**
     * @var array<string, array<array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null}>>
     */
    private array $boosting = [];

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'advanced_search_boosting.written' => 'reset',
        ];
    }

    /**
     * @return array<array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null}>
     */
    public function load(string $salesChannelId, string $streamType): array
    {
        if (\array_key_exists($streamType, $this->boosting)) {
            return $this->boosting[$streamType] = array_filter($this->boosting[$streamType], fn (array $boosting) => $this->isValid($boosting));
        }

        $sql = <<<'SQL'
SELECT
    LOWER(HEX(boosting.id)) AS id,
    LOWER(HEX(boosting.product_stream_id)) AS productStreamId,
    LOWER(HEX(boosting.entity_stream_id)) AS entityStreamId,
    boosting.name AS name,
    boosting.boost AS boost,
    boosting.valid_from AS validFrom,
    boosting.valid_to AS validTo
    FROM advanced_search_boosting boosting
    LEFT JOIN advanced_search_config config ON boosting.config_id = config.id
    WHERE config.sales_channel_id = :salesChannelId AND boosting.active = 1
SQL;

        /** @var array<array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:string, validFrom:string, validTo:string}> $boostings */
        $boostings = $this->connection->fetchAllAssociative(
            $sql,
            [
                'salesChannelId' => Uuid::fromHexToBytes($salesChannelId),
            ],
        );

        $this->boosting = $this->formatBoostings($boostings);

        return $this->boosting[$streamType];
    }

    public function reset(): void
    {
        $this->boosting = [];
    }

    /**
     * @param array<array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:string, validFrom:string, validTo:string}> $boostings
     *
     * @return array<string, array<array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null}>>
     */
    private function formatBoostings(array $boostings): array
    {
        $result = [
            ProductStreamResolver::TYPE => [],
            EntityStreamResolver::TYPE => [],
        ];

        foreach ($boostings as $boosting) {
            /** @var array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null} $boosting */
            $boosting = array_replace($boosting, [
                'boost' => (int) $boosting['boost'],
                'validFrom' => $boosting['validFrom'] ? new \DateTime($boosting['validFrom']) : null,
                'validTo' => $boosting['validTo'] ? new \DateTime($boosting['validTo']) : null,
            ]);

            if (!$this->isValid($boosting)) {
                continue;
            }

            if ($boosting['productStreamId']) {
                $result[ProductStreamResolver::TYPE][] = $boosting;

                continue;
            }

            $result[EntityStreamResolver::TYPE][] = $boosting;
        }

        return $result;
    }

    /**
     * @param array{id:string, productStreamId:string|null, entityStreamId:string|null, name:string, boost:int, validFrom:\DateTime|null, validTo:\DateTime|null} $boosting
     */
    private function isValid(array $boosting): bool
    {
        $validFrom = $boosting['validFrom'];
        $validTo = $boosting['validTo'];

        $now = new \DateTime();
        if ($validFrom === null && $validTo === null) {
            return true;
        }

        if ($validTo === null) {
            return $now >= $validFrom;
        }

        if ($validFrom === null) {
            return $now < $validTo;
        }

        return $now >= $validFrom && $now < $validTo;
    }
}

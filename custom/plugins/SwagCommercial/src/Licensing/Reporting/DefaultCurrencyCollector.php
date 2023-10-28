<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Reporting;

use Doctrine\DBAL\Connection;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
class DefaultCurrencyCollector
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array{isoCode: string, name: string, shortName: string}
     */
    public function collect(): array
    {
        $result = $this->connection->executeQuery(
            '
            SELECT currency.iso_code,
                   currency_translation.name,
                   currency_translation.short_name
            FROM currency
            INNER JOIN currency_translation ON currency.id = currency_translation.currency_id
            INNER JOIN language ON currency_translation.language_id = language.id
            WHERE currency.id = :defaultCurrencyId
                AND language.id = :defaultLanguageId
            ',
            [
                'defaultCurrencyId' => Uuid::fromHexToBytes(Defaults::CURRENCY),
                'defaultLanguageId' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            ]
        );

        /** @var array{iso_code: string, name: string, short_name: string}|false $defaultCurrency */
        $defaultCurrency = $result->fetchAssociative();

        if (!\is_array($defaultCurrency)) {
            throw new \RuntimeException('Failed to fetch default currency');
        }

        return [
            'isoCode' => $defaultCurrency['iso_code'],
            'name' => $defaultCurrency['name'],
            'shortName' => $defaultCurrency['short_name'],
        ];
    }
}

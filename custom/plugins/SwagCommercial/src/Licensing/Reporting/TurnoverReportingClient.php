<?php declare(strict_types=1);

namespace Shopware\Commercial\Licensing\Reporting;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Store\Authentication\AbstractStoreRequestOptionsProvider;

/**
 * @final
 *
 * @internal
 */
#[Package('merchant-services')]
class TurnoverReportingClient
{
    public function __construct(
        private readonly string $endpoint,
        private readonly Client $client,
        private readonly AbstractStoreRequestOptionsProvider $optionsProvider
    ) {
    }

    /**
     * @param array{version: int, turnover: array<string, mixed>, canceled: array<string, mixed>, reopened: array<string, mixed>, defaultCurrency: array<string, mixed>} $turnoverReport
     */
    public function reportTurnover(array $turnoverReport, Context $context): void
    {
        $headers = $this->optionsProvider->getAuthenticationHeader($context);

        if (isset($headers['X-Shopware-Platform-Token'])) {
            unset($headers['X-Shopware-Platform-Token']);
        }

        $this->client->post($this->endpoint, [
            RequestOptions::HEADERS => $headers,
            RequestOptions::QUERY => $this->optionsProvider->getDefaultQueryParameters($context),
            RequestOptions::JSON => $turnoverReport,
        ]);
    }
}

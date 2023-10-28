<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Shopware\Commercial\FlowBuilder\WebhookFlowAction\Domain\Action;

use Doctrine\DBAL\Connection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Content\Flow\Dispatching\Action\FlowAction;
use Shopware\Core\Content\Flow\Dispatching\DelayableAction;
use Shopware\Core\Content\Flow\Dispatching\StorableFlow;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Twig\StringTemplateRenderer;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Webhook\EventLog\WebhookEventLogDefinition;

/**
 * @internal
 */
#[Package('business-ops')]
class CallWebhookAction extends FlowAction implements DelayableAction
{
    private const TIMEOUT = 20;
    private const CONNECT_TIMEOUT = 10;

    /**
     * @internal
     */
    public function __construct(
        private Client $guzzleClient,
        private StringTemplateRenderer $templateRenderer,
        private LoggerInterface $logger,
        private Connection $connection
    ) {
    }

    public static function getName(): string
    {
        return 'action.call.webhook';
    }

    public function requirements(): array
    {
        return [];
    }

    public function handleFlow(StorableFlow $flow): void
    {
        if (!License::get('FLOW_BUILDER-7563087')) {
            return;
        }

        $config = $flow->getConfig();
        if (!$this->validateConfigData($config)) {
            return;
        }

        /** @var array<string, mixed> $options */
        $options = $config['options'] ?? [];

        $options['connect_timeout'] = self::CONNECT_TIMEOUT;
        $options['timeout'] = self::TIMEOUT;

        if (\array_key_exists(RequestOptions::AUTH, $options) && !$config['authActive']) {
            unset($options[RequestOptions::AUTH]);
        }

        $sequenceId = $flow->getFlowState()->getSequenceId();
        $options = $this->buildRequestOptions($options, $flow->data(), $flow->getContext());

        $this->callWebhook($sequenceId, $flow->getName(), $config, $options);
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $config
     */
    private function callWebhook(string $sequenceId, string $eventName, array $config, array $options): void
    {
        $webhookEventId = Uuid::randomBytes();
        $timestamp = \time();

        $this->connection->executeStatement(
            'INSERT INTO
                `webhook_event_log` (id, delivery_status, timestamp, webhook_name, event_name, url, request_content, created_at)
                VALUES (:webhookEventId, :deliveryStatus, :timestamp, :webhookName, :eventName, :url, :requestContent, :createdAt)',
            [
                'webhookEventId' => $webhookEventId,
                'deliveryStatus' => WebhookEventLogDefinition::STATUS_RUNNING,
                'timestamp' => $timestamp,
                'webhookName' => $config['method'] . ': ' . $config['baseUrl'],
                'eventName' => $eventName,
                'url' => $config['baseUrl'],
                'requestContent' => \json_encode([
                    'method' => $config['method'],
                    'options' => $options,
                ]),
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ],
        );

        $this->connection->executeStatement(
            'INSERT INTO `swag_sequence_webhook_event_log` (sequence_id, webhook_event_log_id)
                VALUES (:sequenceId, :webhookEventId)',
            [
                'sequenceId' => Uuid::fromHexToBytes($sequenceId),
                'webhookEventId' => $webhookEventId,
            ]
        );

        try {
            /** @var string $method */
            $method = $config['method'];

            /** @var string $baseUrl */
            $baseUrl = $config['baseUrl'];
            $response = $this->guzzleClient->request($method, $baseUrl, $options);

            $this->connection->executeStatement(
                'UPDATE `webhook_event_log`
                    SET delivery_status = :deliveryStatus, processing_time = :processingTime,
                        response_content = :responseContent, response_status_code = :responseStatusCode,
                        response_reason_phrase = :responseReasonPhrase
                    WHERE id = :webhookEventId',
                [
                    'webhookEventId' => $webhookEventId,
                    'deliveryStatus' => WebhookEventLogDefinition::STATUS_SUCCESS,
                    'processingTime' => \time() - $timestamp,
                    'responseContent' => \json_encode([
                        'headers' => $response->getHeaders(),
                        'body' => \json_decode($response->getBody()->getContents(), true),
                    ]),
                    'responseStatusCode' => $response->getStatusCode(),
                    'responseReasonPhrase' => $response->getReasonPhrase(),
                ],
            );
        } catch (GuzzleException $e) {
            $this->logger->notice(\sprintf('Webhook execution failed to target url "%s".', $baseUrl), [
                'exceptionMessage' => $e->getMessage(),
                'statusCode' => $e->getCode(),
            ]);

            $payload = [
                'webhookEventId' => $webhookEventId,
                'deliveryStatus' => WebhookEventLogDefinition::STATUS_FAILED,
                'processingTime' => \time() - $timestamp,
            ];

            if ($e instanceof RequestException && $e->getResponse() !== null) {
                $response = $e->getResponse();
                $payload = \array_merge($payload, [
                    'responseContent' => \json_encode([
                        'headers' => $response->getHeaders(),
                        'body' => \json_decode($response->getBody()->getContents(), true),
                    ]),
                    'responseStatusCode' => $response->getStatusCode(),
                    'responseReasonPhrase' => $response->getReasonPhrase(),
                ]);

                $this->connection->executeStatement(
                    'UPDATE `webhook_event_log`
                        SET delivery_status = :deliveryStatus, processing_time = :processingTime,
                            response_content = :responseContent, response_status_code = :responseStatusCode,
                            response_reason_phrase = :responseReasonPhrase
                        WHERE id = :webhookEventId',
                    $payload
                );
            }
        }
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function buildRequestOptions(array $options, array $data, Context $context): array
    {
        /*
         * request headers:
         * $options['headers'] = [
         *     'Content-Type' => 'application/json',
         *     'User-Agent' => 'GuzzleHttp/7',
         * ]
         */
        if (\array_key_exists(RequestOptions::HEADERS, $options)) {
            /** @var array<string, mixed> $headers */
            $headers = $options[RequestOptions::HEADERS];
            $options[RequestOptions::HEADERS] = $this->resolveOptionParams($headers, $data, $context);
        }

        /*
         * request query:
         * $options['query'] = [
         *     'orderNumber' => '{{ order.orderNumber }}',
         *     'message' => 'message test',
         * ]
         */
        if (\array_key_exists(RequestOptions::QUERY, $options)) {
            /** @var array<string, mixed> $queries */
            $queries = $options[RequestOptions::QUERY];
            $options[RequestOptions::QUERY] = $this->resolveOptionParams($queries, $data, $context);
        }

        /*
         * request form params:
         * $options['form_params'] = [
         *     'firstName' => '{{ customer.firstName }}',
         *     'message' => 'Foo',
         * ]
         */
        if (\array_key_exists(RequestOptions::FORM_PARAMS, $options)) {
            /** @var array<string, mixed> $params */
            $params = $options[RequestOptions::FORM_PARAMS];
            $options[RequestOptions::FORM_PARAMS] = $this->resolveOptionParams($params, $data, $context);
        }

        /*
         * request body:
         * $options['body'] = 'Foo bar!'
         * or
         * $options['body'] = '{"chat_id": "332293824", "text": "Hello {{ customer.firstName }}"}'
         */
        if (\array_key_exists(RequestOptions::BODY, $options)) {
            /** @var string $template */
            $template = $options[RequestOptions::BODY];
            $options[RequestOptions::BODY] = $this->resolveParamsData($template, $data, $context);
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    private function resolveOptionParams(array $params, array $data, Context $context): array
    {
        /** @var string $value */
        foreach ($params as $key => $value) {
            $params[$key] = $this->resolveParamsData($value, $data, $context);
        }

        return $params;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveParamsData(string $template, array $data, Context $context): ?string
    {
        try {
            return $this->templateRenderer->render($template, $data, $context);
        } catch (\Throwable $e) {
            $this->logger->error(
                "Could not render template with error message:\n"
                . $e->getMessage() . "\n"
                . 'Error Code:' . $e->getCode() . "\n"
                . 'Template source:'
                . $template . "\n"
                . "Template data: \n"
                . \json_encode($data) . "\n"
            );

            return null;
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function validateConfigData(array $config): bool
    {
        if (!\array_key_exists('method', $config)) {
            $this->logger->error(
                "Method does not exist in config data:\n"
                . \json_encode($config) . "\n"
            );

            return false;
        }

        if (!\array_key_exists('baseUrl', $config)) {
            $this->logger->error(
                "Base url does not exist in config data:\n"
                . \json_encode($config) . "\n"
            );

            return false;
        }
        if (!\array_key_exists('authActive', $config)) {
            $this->logger->error(
                "Auth active does not exist in config data:\n"
                . \json_encode($config) . "\n"
            );

            return false;
        }

        return true;
    }
}

<?php declare(strict_types=1);

namespace Shopware\Commercial\ExportAssistant\Service;

use GuzzleHttp\ClientInterface;
use Shopware\Commercial\ExportAssistant\Exception\ExportAssistantException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @final
 *
 * @internal
 */
#[Package('system-settings')]
class CriteriaGenerator
{
    private const AI_CRITERIA_GENERATION_ENDPOINT = 'https://ai-services.apps.shopware.io/api/criteria/generate';

    /**
     * @internal
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly SystemConfigService $configService,
        private readonly RequestCriteriaBuilder $requestCriteriaBuilder,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry
    ) {
    }

    /**
     * @return array{entity: string, criteria: array<string, mixed>}
     */
    public function generate(string $prompt, ?string $entity = null): array
    {
        if (!License::get('EXPORT_ASSISTANT-0490710')) {
            throw new LicenseExpiredException();
        }

        $res = $this->client->request(Request::METHOD_POST, self::AI_CRITERIA_GENERATION_ENDPOINT, [
            'json' => [
                'message' => $prompt,
                'entity' => $entity,
            ],
            'headers' => [
                'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        /** @var array{entity: string, criteria: array<string, mixed>} $response */
        $response = json_decode($res->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        $this->validateCriteria($response, $prompt);

        return $response;
    }

    /**
     * @param array{entity: string, criteria: array<string, mixed>} $response
     */
    private function validateCriteria(array $response, string $prompt): void
    {
        if (empty($response['entity']) || !$this->definitionInstanceRegistry->has($response['entity'])) {
            throw ExportAssistantException::cannotDetectEntity($prompt);
        }

        $definition = $this->definitionInstanceRegistry->getByEntityName($response['entity']);

        if (empty($response['criteria'])) {
            throw ExportAssistantException::cannotGenerateCriteria($prompt);
        }

        $context = new Context(new SystemSource(), [], Defaults::CURRENCY, [Defaults::LANGUAGE_SYSTEM]);

        try {
            $response['criteria']['filter'] = $response['criteria']['filters'];
            unset($response['criteria']['filters']);

            $this->requestCriteriaBuilder->fromArray($response['criteria'], new Criteria(), $definition, $context);
        } catch (\Throwable $error) {
            throw ExportAssistantException::cannotGenerateCriteria($error->getMessage(), $prompt);
        }
    }
}

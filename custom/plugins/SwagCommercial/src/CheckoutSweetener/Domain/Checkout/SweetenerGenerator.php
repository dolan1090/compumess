<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener\Domain\Checkout;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\Locale\LocaleEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @final
 *
 * @internal
 */
#[Package('checkout')]
class SweetenerGenerator
{
    private const AI_CHECKOUT_SWEETENER_ENDPOINT = 'https://ai-services.apps.shopware.io/api/checkout-sweetener/generate';

    public function __construct(
        private readonly EntityRepository $localeRepository,
        private readonly ClientInterface $client,
        private readonly SystemConfigService $configService
    ) {
    }

    /**
     * @param array<string, mixed> $generationContext
     *
     * @throws GuzzleException
     */
    public function generate(array $generationContext, Context $context): SweetenerResponse
    {
        if (!License::get('CHECKOUT_SWEETENER-8945908')) {
            throw new LicenseExpiredException();
        }

        $generationContext = $this->buildPayload($generationContext, $context);

        $response = $this->client->request(Request::METHOD_POST, self::AI_CHECKOUT_SWEETENER_ENDPOINT, [
            'json' => $generationContext,
            'headers' => [
                'Authorization' => $this->configService->getString(License::CONFIG_STORE_LICENSE_KEY),
            ],
        ]);

        $response = \json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);

        if (!\is_array($response)) {
            throw new \RuntimeException('Invalid response from text generator.');
        }

        $responseObject = new SweetenerResponse();
        $responseObject->assign($response);

        return $responseObject;
    }

    /**
     * @param array<string, mixed> $generationContext
     *
     * @throws \InvalidArgumentException
     *
     * @return array<string, mixed> $generationContext
     */
    protected function buildPayload(array $generationContext, Context $context): array
    {
        if (empty($generationContext['products']) || !\is_array($generationContext['products'])) {
            throw new \InvalidArgumentException('Missing required parameter products.');
        }

        if (empty($generationContext['keywords']) || !\is_array($generationContext['keywords'])) {
            throw new \InvalidArgumentException('Missing required parameter keywords.');
        }

        $generationContext['locale'] = $this->getLocale($context->getLanguageId(), $context);

        return $generationContext;
    }

    private function getLocale(string $languageId, Context $context): string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('languages.id', $languageId));

        /** @var LocaleEntity|null $locale */
        $locale = $this->localeRepository->search($criteria, $context)->first();

        if ($locale === null) {
            throw new \InvalidArgumentException(sprintf('Could not find locale for languageId "%s"', $languageId));
        }

        return $locale->getCode();
    }
}

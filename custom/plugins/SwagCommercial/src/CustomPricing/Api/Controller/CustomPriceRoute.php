<?php declare(strict_types=1);

namespace Shopware\Commercial\CustomPricing\Api\Controller;

use Shopware\Commercial\CustomPricing\Domain\CustomPriceUpdater;
use Shopware\Core\Framework\Api\Sync\SyncResult;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @phpstan-import-type  CustomPriceUploadType from CustomPriceUpdater
 * @phpstan-import-type  CustomPriceDeleteType from CustomPriceUpdater
 */
#[Package('inventory')]
#[Route(defaults: ['_routeScope' => ['api']])]
class CustomPriceRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly CustomPriceUpdater $customPriceUpdater)
    {
    }

    #[Route(
        path: '/api/_action/custom-price',
        name: 'commercial.api.custom_price.import',
        defaults: ['_acl' => ['custom_price.creator']],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CUSTOM_PRICES-2356553\')'
    )]
    public function import(Request $request): JsonResponse
    {
        /**
         * @var array<int, array{action: string, payload: list<CustomPriceUploadType|CustomPriceDeleteType>}> $operations
         */
        $operations = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        $syncResult = $this->customPriceUpdater->sync($operations);

        return $this->createResponse($syncResult);
    }

    private function createResponse(SyncResult $result): JsonResponse
    {
        $response = new JsonResponse(null, Response::HTTP_OK);
        $response->setEncodingOptions(JsonResponse::DEFAULT_ENCODING_OPTIONS | \JSON_INVALID_UTF8_SUBSTITUTE);
        $response->setData($result);

        return $response;
    }
}

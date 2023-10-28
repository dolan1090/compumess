<?php declare(strict_types=1);

namespace Shopware\Commercial\ClassificationCustomer\Api;

use Shopware\Commercial\ClassificationCustomer\Domain\Customer\ClassificationCustomerService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @phpstan-import-type GenerateTagsData from ClassificationCustomerService
 * @phpstan-import-type ClassifyCustomerData from ClassificationCustomerService
 */
#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['api']])]
class ClassificationCustomerController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(private readonly ClassificationCustomerService $classificationCustomerService)
    {
    }

    #[Route(
        path: '/api/_action/classification-customer/classify',
        name: 'commercial.api.classification-customer.classify',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CUSTOMER_CLASSIFICATION-8266203\')'
    )]
    public function classify(Request $request, Context $context): JsonResponse
    {
        /** @var ClassifyCustomerData $options */
        $options = $request->request->all();

        $this->classificationCustomerService->classify($options, $context);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(
        path: '/api/_action/classification-customer/generate-tags',
        name: 'commercial.api.classification-customer.generate-tags',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CUSTOMER_CLASSIFICATION-8266203\')'
    )]
    public function generateTags(Request $request): JsonResponse
    {
        /** @var GenerateTagsData $options */
        $options = $request->request->all();

        $result = $this->classificationCustomerService->generateTag($options);

        return new JsonResponse($result);
    }
}

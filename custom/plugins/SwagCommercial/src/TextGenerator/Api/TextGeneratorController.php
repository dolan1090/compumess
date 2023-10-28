<?php declare(strict_types=1);

namespace Shopware\Commercial\TextGenerator\Api;

use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\TextGenerator\Domain\Product\DescriptionGenerator;
use Shopware\Core\Framework\Log\Package;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @final
 *
 * @internal
 */
#[Package('inventory')]
#[Route(defaults: ['_routeScope' => ['api']])]
class TextGeneratorController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(private readonly DescriptionGenerator $productDescriptionGenerator)
    {
    }

    #[Route(
        path: '/api/_action/generate-product-description',
        name: 'commercial.api.generate_product_description.get',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'TEXT_GENERATOR-2946372\')'
    )]
    public function generate(Request $request): JsonResponse
    {
        /** @var array<string, string> $options */
        $options = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        try {
            $description = $this->productDescriptionGenerator->generate($options);
        } catch (\InvalidArgumentException|GuzzleException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($description);
    }
}

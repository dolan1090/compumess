<?php declare(strict_types=1);

namespace Shopware\Commercial\TextTranslator\Storefront;

use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\TextTranslator\Domain\Product\ReviewTranslator;
use Shopware\Commercial\TextTranslator\Exception\ReviewTranslatorException;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @internal
 */
#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('inventory')]
class ReviewTranslatorController extends StorefrontController
{
    public function __construct(private readonly ReviewTranslator $reviewTranslator)
    {
    }

    #[Route(
        path: '/translate-review',
        name: 'frontend.product.review.translate',
        options: ['seo' => false],
        defaults: ['_noStore' => true, 'XmlHttpRequest' => true],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'REVIEW_TRANSLATOR-1649854\')'
    )]
    public function translateReview(RequestDataBag $data, SalesChannelContext $context): Response
    {
        try {
            $translation = $this->reviewTranslator->translate($data->getAlnum('reviewId'), $context->getLanguageId());
        } catch (ReviewTranslatorException|GuzzleException $e) {
            return $this->createJsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return $this->createJsonResponse($translation);
    }

    /**
     * @param array<string, mixed>|string $data
     */
    private function createJsonResponse(array|string $data, int $code = Response::HTTP_OK): JsonResponse
    {
        $response = new JsonResponse($data, $code);
        $response->headers->set('x-robots-tag', 'noindex,nofollow');

        return $response;
    }
}

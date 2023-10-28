<?php declare(strict_types=1);

namespace Shopware\Commercial\ReviewSummary\Api;

use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\ReviewSummary\Domain\Product\Review\GenerationContextFactory;
use Shopware\Commercial\ReviewSummary\Domain\Product\Review\ReviewSummaryGenerator;
use Shopware\Commercial\ReviewSummary\Exception\ReviewSummaryException;
use Shopware\Core\Framework\Context;
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
class ReviewSummaryGeneratorController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly ReviewSummaryGenerator $reviewSummaryGenerator,
        private readonly GenerationContextFactory $generationContextFactory,
    ) {
    }

    #[Route(
        path: '/api/_action/generate-review-summary',
        name: 'commercial.api.generate_review_summary.get',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'REVIEW_SUMMARY-8147095\')'
    )]
    public function generate(Request $request, Context $context): JsonResponse
    {
        /** @var array<string, string> $options */
        $options = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        try {
            $generateContext = $this->generationContextFactory->create($options, $context);
            $summaries = $this->reviewSummaryGenerator->generate($generateContext);
        } catch (ReviewSummaryException|GuzzleException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($summaries);
    }
}

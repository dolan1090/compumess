<?php declare(strict_types=1);

namespace Shopware\Commercial\CheckoutSweetener\Api;

use GuzzleHttp\Exception\GuzzleException;
use Shopware\Commercial\CheckoutSweetener\Domain\Checkout\SweetenerGenerator;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
#[Package('checkout')]
#[Route(defaults: ['_routeScope' => ['api']])]
class SweetenerController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(
        private readonly SweetenerGenerator $checkoutSweetenerGenerator,
        private readonly EntityRepository $productRepository,
    ) {
    }

    #[Route(
        path: '/api/_action/generate-checkout-sweetener',
        name: 'commercial.api.generate_checkout-sweetener.get',
        methods: ['POST'],
        condition: 'service(\'license\').check(\'CHECKOUT_SWEETENER-8945908\')'
    )]
    public function generateSweetener(Request $request, Context $context): JsonResponse
    {
        /** @var array<string, mixed> $options */
        $options = \json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);

        if (isset($options['productIds']) && \is_array($options['productIds'])) {
            $ids = \array_filter($options['productIds'], static fn ($id) => \is_string($id));
            $options['products'] = array_values($this->productRepository->search(new Criteria($ids), $context)->map(static function (ProductEntity $product) {
                return $product->getName();
            }));
        }

        try {
            $description = $this->checkoutSweetenerGenerator->generate($options, $context);
        } catch (\InvalidArgumentException|GuzzleException $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($description);
    }
}

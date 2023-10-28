<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Route;

use OpenApi\Annotations as OA;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Event\CartCreatedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Seo\SeoUrlPlaceholderHandlerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsCartError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Error\SwagCustomizedProductsPriceCalculationError;
use Swag\CustomizedProducts\Core\Checkout\Cart\Route\AbstractAddCustomizedProductsToCartRoute;
use Swag\CustomizedProducts\Core\Checkout\CustomizedProductsCartDataCollector;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\PriceDetailCalculationExtension;
use Swag\CustomizedProducts\Storefront\Page\Product\PriceDetail\Route\PriceDetailRoute;
use Swag\CustomizedProducts\Storefront\Page\Product\ProductPageSubscriber;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service\TemplateConfigurationServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"store-api"}})
 */
class CreateConfigurationShareRoute extends AbstractCreateConfigurationShareRoute
{
    final public const ONE_TIME_SHARE_PARAMETER = 'swag-customized-products-one-time-share';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractAddCustomizedProductsToCartRoute $addCustomizedProductsToCartRoute,
        private readonly TemplateConfigurationServiceInterface $configurationService,
        private readonly SeoUrlPlaceholderHandlerInterface $seoUrlPlaceholderHandler,
        private readonly EntityRepository $configurationShareRepository
    ) {
    }

    public function getDecorated(): AbstractCreateConfigurationShareRoute
    {
        throw new DecorationPatternException(self::class);
    }

    /**
     * @OA\Post(
     *     path="/customized-products/configuration/create-share",
     *     description="Creates a share link for your configuration",
     *     operationId="customizedProductConfigurationShare",
     *     tags={"Store API", "Customized Products"},
     *
     *     @OA\Parameter(
     *         parameter="customized-products-template",
     *         name="customized-products-template",
     *         in="body",
     *         description="The template configuration",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(
     *                 property="id",
     *                 description="The template id this configuration is for",
     *                 type="string",
     *                 format="uuid"
     *             ),
     *             @OA\Property(
     *                 property="options",
     *                 description="An array of options and their values",
     *                 type="object"
     *             ),
     *             example={"id": "19489f5e16e14ac8b7c1dad26a258923", "options": { "b7d2554b0ce847cd82f3ac9bd1c0dfca": { "value": "Example textfield value" }}}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Property(
     *                 property="shareUrl",
     *                 type="string"
     *             ),
     *             example={"shareUrl": "https://example.com/Example/SW10000?swagCustomizedProductConfigurationShare=0CFD39E7A5EE4CBDBD223956CB40AB55"}
     *         )
     *     )
     * )
     *
     * @Route("/store-api/customized-products/configuration/create-share", name="store-api.customized-products.configuration.create-share", methods={"POST"})
     */
    public function createConfigurationShare(Request $request, SalesChannelContext $context): ConfigurationShareCreatedResponse
    {
        $cart = new Cart(Uuid::randomHex());
        $cart->addExtension(
            PriceDetailRoute::PRICE_DETAIL_CALCULATION_EXTENSION_KEY,
            new PriceDetailCalculationExtension()
        );
        $this->eventDispatcher->dispatch(new CartCreatedEvent($cart));
        $this->addCustomizedProductsToCartRoute->add(new RequestDataBag($request->request->all()), $request, $context, $cart);

        $customizedProductLineItem = $cart->getLineItems()->first();
        if ($customizedProductLineItem === null) {
            $customizedProductCartError = $cart->getErrors()->filterInstance(SwagCustomizedProductsCartError::class)->first();

            throw $customizedProductCartError ?? new SwagCustomizedProductsPriceCalculationError();
        }

        $productLineItem = $customizedProductLineItem->getChildren()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->first();
        if ($productLineItem === null) {
            throw new SwagCustomizedProductsPriceCalculationError();
        }

        $configuration = $this->configurationService->getTemplateConfiguration(
            $customizedProductLineItem,
            $customizedProductLineItem->getPayloadValue(CustomizedProductsCartDataCollector::CUSTOMIZED_PRODUCTS_TEMPLATE_LINE_ITEM_CONFIGURATION_HASH),
            $context->getContext()
        );
        if ($configuration === null) {
            throw new SwagCustomizedProductsPriceCalculationError();
        }

        $host = $request->request->get('absoluteBaseUrl')
            . $request->request->get('baseUrl');

        $productSeoUrl = $this->seoUrlPlaceholderHandler->replace(
            $this->seoUrlPlaceholderHandler->generate(
                'frontend.detail.page',
                [
                    'productId' => $productLineItem->getReferencedId(),
                ]
            ),
            $host,
            $context
        );

        return new ConfigurationShareCreatedResponse(
            \sprintf(
                '%s?%s=%s',
                $productSeoUrl,
                ProductPageSubscriber::SHARE_CONFIGURATION_PARAMETER,
                $this->createShareForConfiguration(
                    $configuration->getId(),
                    $context->getContext(),
                    $request->request->getBoolean(self::ONE_TIME_SHARE_PARAMETER)
                )
            )
        );
    }

    private function createShareForConfiguration(string $configurationId, Context $context, bool $oneTime = false): string
    {
        $shareId = Uuid::randomHex();
        $this->configurationShareRepository->create(
            [
                [
                    'id' => $shareId,
                    'templateConfigurationId' => $configurationId,
                    'oneTime' => $oneTime,
                ],
            ],
            $context
        );

        return $shareId;
    }
}

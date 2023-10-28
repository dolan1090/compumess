<?php

declare(strict_types=1);

namespace Eseom\OrderForm\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;

#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('storefront')]

class OrderFormController extends StorefrontController {

    /**
     * @var SalesChannelRepository
     */
    private $salesChannelProductRepository;

    /**
     * @var EntityRepository
     */
    private $mediaRepository;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var CartService
     */
    private $cartService;

    /**
     * @var LineItemFactoryRegistry
     */
    private $factory;

    /**
     * @var SystemConfigService
     */
    protected $systemConfig;

    public function __construct(
        SalesChannelRepository $salesChannelProductRepository,
        EntityRepository $mediaRepository,
        Translator $translator,
        CartService $cartService,
        LineItemFactoryRegistry $factory,
        SystemConfigService $systemConfig) {
        $this->salesChannelProductRepository = $salesChannelProductRepository;
        $this->mediaRepository = $mediaRepository;
        $this->translator = $translator;
        $this->cartService = $cartService;
        $this->factory = $factory;
        $this->systemConfig = $systemConfig;
    }

    #[Route(path:"/eseom-order-form/add-to-cart", name:"frontend.eseomorderform.addtocart", options:["seo"=>"false"], methods:["POST"], defaults:["XmlHttpRequest"=>true])]

    public function orderFormAddProducts(Request $request, Cart $cart, SalesChannelContext $context): JsonResponse {
        $configFields = $this->systemConfig->get('EseomOrderForm.config', $context->getSalesChannelId());
        $positions = $request->get('positions');

        /* BEGIN - VALIDATION */
        if (sizeof($positions) > 0) {
            foreach ($positions as $position) {
                $originalProductNumber = trim($position['productNr']);
                $productNumber = trim($position['productNr']);
                $productQuantity = intval($position['productQuantity']);

                if (empty($productNumber)) {
                    return new JsonResponse(
                        [
                            'success' => 0,
                            'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg4", [])
                        ]
                    );
                }

                if ($productQuantity < 1) {
                    return new JsonResponse(
                        [
                            'success' => 0,
                            'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg5", ['%productNumber%' => $originalProductNumber])
                        ]
                    );
                }

                $criteria = new Criteria();
                /* BEGIN - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
                if (isset($configFields) && sizeof($configFields) > 0) {
                    foreach ($configFields as $key => $value) {
                        if ($key === 'eseomOrderFormExcludeProducts') {
                            if (!empty($value)) {
                                $criteria->addFilter(new NotFilter('AND', [new EqualsAnyFilter('id', $value)]));
                            }
                        }
                    }
                }
                /* END - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
                $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
                $criteria->setLimit(1);

                $foundProducts = $this->salesChannelProductRepository->search(
                    $criteria, $context
                );

                if (sizeof($foundProducts) === 0) {
                    return new JsonResponse(
                        [
                            'success' => 0,
                            'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg6", ['%productNumber%' => $originalProductNumber])
                        ]
                    );
                } else {
                    foreach ($foundProducts as $foundProduct) {
                        /* BEGIN - CHECK WHETHER THE PRODUCT IS A MAIN PRODUCT AND HAS CHILDREN/VARIANTS - ONLY THE VARIANTS/CHILDS CAN BE ADDED TO THE CART */
                        if ($foundProduct->getParentId() === null && $foundProduct->getChildCount() > 0) {
                            return new JsonResponse(
                                [
                                    'success' => 0,
                                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg7", ['%productNumber%' => $originalProductNumber])
                                ]
                            );
                        }
                        /* END - CHECK WHETHER THE PRODUCT IS A MAIN PRODUCT AND HAS CHILDREN/VARIANTS - ONLY THE VARIANTS/CHILDS CAN BE ADDED TO THE CART */

                        $qtySumSameProducts = 0;
                        foreach ($positions as $position) {
                            if (trim($position['productNr']) === $originalProductNumber) {
                                $qtySumSameProducts += intval($position['productQuantity']);
                            }
                        }

                        $isValidProductQuantity = $this->isValidProductQuantity($foundProduct, $qtySumSameProducts, $cart, $context);
                        $isValidProductQuantityDecoded = json_decode($isValidProductQuantity->getContent(), true);
                        if ($isValidProductQuantityDecoded['success'] === 0) {
                            return $isValidProductQuantity;
                        }
                    }
                }
            }
        } else {
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg3", [])
                ]
            );
        }
        /* END - VALIDATION */

        /* BEGIN - ADD PRODUCTS TO CART */
        //$lineItemsToCartArr = [];
        $lineItemsToCartCollection = new LineItemCollection();

        foreach ($positions as $position) {
            $productNumber = trim($position['productNr']);
            $productQuantity = intval($position['productQuantity']);

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
            $criteria->setLimit(1);

            $foundProducts = $this->salesChannelProductRepository->search(
                $criteria, $context
            );

            foreach ($foundProducts as $foundProduct) {
//                $lineItemInCart = $this->getLineItemInCart($cart, $foundProduct->getId());
//                if ($lineItemInCart !== null) {
//                    $this->cartService->changeQuantity($cart, $lineItemInCart['id'], $lineItemInCart['productQuantity'] + $productQuantity, $context);
//               } else {
                $lineItem = $this->factory->create([
                    'id' => $foundProduct->getId(),
                    'type' => LineItem::PRODUCT_LINE_ITEM_TYPE, // Results in 'product'
                    'referencedId' => $foundProduct->getId(),
                    'quantity' => $productQuantity/* ,
                          'payload' => ['key' => 'value'] */
                ], $context);
// $lineItem->setStackable(false);
                //$lineItemsToCartArr[] = $lineItem;
                $lineItemsToCartCollection->add($lineItem);
//                }
            }
        }
        //$cart = $this->cartService->add($cart, $lineItemsToCartArr, $context);
        $cart->addLineItems($lineItemsToCartCollection);
        $cart = $this->cartService->recalculate($cart, $context);
        /* END - ADD PRODUCTS TO CART */

        if (count($cart->getErrors()) > 0) {
            $errorMsgs = "";
            foreach ($cart->getErrors() as $cartError) {
                $translatedErrorMsg = $this->translator->trans($cartError->getMessageKey(), []);
                if ($translatedErrorMsg === $cartError->getMessageKey()) {
                    $translatedErrorMsg = $this->translator->trans('checkout.' . $cartError->getMessageKey(), ['%name%' => $cartError->getName(), '%quantity%' => $cartError->getQuantity()]);
                }
                if ($translatedErrorMsg === 'checkout.' . $cartError->getMessageKey()) {
                    $translatedErrorMsg = $this->translator->trans('error.' . $cartError->getMessageKey(), ['%name%' => $cartError->getName(), '%quantity%' => $cartError->getQuantity]);
                }
                $errorMsgs .= $translatedErrorMsg;
            }
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $errorMsgs,
                    'cartTotal' => $this->cartService->getCart($cart->getToken(), $context)->getPrice()->getPositionPrice()
                ]
            );
        }

        return new JsonResponse(
            [
                'success' => 1,
                'msg' => $this->translator->trans("eseom-order-form.successModal.successMsg1", []),
                'cartTotal' => $this->cartService->getCart($cart->getToken(), $context)->getPrice()->getPositionPrice()
            ]
        );
    }

    /*
     * BEGIN - CHECK WHETHER PRODUCT IS ALREADY IN CART
     * IF YES WE HAVE TO CALL $this->cartService->changeQuantity
     * IF NO WE HAVE TO CALL $this->cartService->add
     * OTHERWISE IT WOULD CREATE A NEW LINEITEM POSITION FOR EACH ADDED PRODUCT AND THE QUANTITIES WILL NOT BE ADDED UP
     */

    private function getLineItemInCart(Cart $cart, string $addedProductId): ?array {
        $responseArr = null;
        $cartLineItems = $cart->getLineItems();

        foreach ($cartLineItems as $cartLineItem) {
            if ($cartLineItem->getReferencedId() === $addedProductId) {
                $responseArr = [];
                $responseArr['id'] = $cartLineItem->getId();
                $responseArr['productQuantity'] = $cartLineItem->getQuantity();

                $deliveryTimeName = $cartLineItem->getDeliveryInformation()->getDeliveryTime() ? $cartLineItem->getDeliveryInformation()->getDeliveryTime()->getName() : "-";
                $restockTimeName = $cartLineItem->getDeliveryInformation()->getRestockTime() ? $cartLineItem->getDeliveryInformation()->getRestockTime() : "-";
                $responseArr['productDeliveryInformation'] = $deliveryTimeName;
                $responseArr['productRestockTime'] = $restockTimeName;
                $responseArr['price'] = $cartLineItem->getPrice()->getTotalPrice() / $cartLineItem->getQuantity();
                break;
            }
        }
        return $responseArr;
    }

    /*
     * END - CHECK WHETHER PRODUCT IS ALREADY IN CART
     * IF YES WE HAVE TO CALL $this->cartService->changeQuantity
     * IF NO WE HAVE TO CALL $this->cartService->add
     * OTHERWISE IT WOULD CREATE A NEW LINEITEM POSITION FOR EACH ADDED PRODUCT AND THE QUANTITIES WILL NOT BE ADDED UP
     */

    #[Route(path:"/eseom-order-form/get-product-info", name:"frontend.eseomorderform.getproductinfo", options:["seo"=>"false"], methods:["POST"], defaults:["XmlHttpRequest"=>true])]

    public function orderFormGetProductInfo(Request $request, Cart $cart, SalesChannelContext $context): JsonResponse {
        $configFields = $this->systemConfig->get('EseomOrderForm.config', $context->getSalesChannelId());
        $productNumber = trim($request->get('productNumber'));
        $productQuantity = intval($request->get('productQuantity'));
        $productName = "";
        $productCoverImageUrl = "";
        $productPrice = 99999999.99;
        $productAvailableStock = 0;
        $productAvailableStockTxt = "";
        $productDescription = "";
        $productEAN = "";
        $productBaseUrl = "";
        $productUrl = "";
        $productVariationOptions = [];
        $productProperties = [];
        $showProductStockInModal = true;

        if (empty($productNumber)) {
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg1", [])
                ]
            );
        }

        /* BEGIN - SEARCH AFTER PRODUCT WITH PRODUCTNUMBER */
        $criteria = new Criteria();
        /* BEGIN - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        if (isset($configFields) && sizeof($configFields) > 0) {
            foreach ($configFields as $key => $value) {
                if ($key === 'eseomOrderFormExcludeProducts') {
                    if (!empty($value)) {
                        $criteria->addFilter(new NotFilter('AND', [new EqualsAnyFilter('id', $value)]));
                    }
                }
                if ($key === 'eseomOrderFormModalShowStock') {
                    $showProductStockInModal = boolval($value);
                }
            }
        }
        /* END - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $criteria->addAssociation('cover');
        $criteria->addAssociation('seoUrls');
        $criteria->addAssociation('properties');
        $criteria->addAssociation('properties.group');
        $criteria->addAssociation('options');
        $criteria->addAssociation('options.group');
        $criteria->setLimit(1);

        $foundProducts = $this->salesChannelProductRepository->search(
            $criteria, $context
        );
        /* END - SEARCH AFTER PRODUCT WITH PRODUCTNUMBER */

        if (sizeof($foundProducts) === 0) {
            /*
             * $this->container
              ->get('translator')
              ->trans($snippet, $parameters)
             */
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator
                        ->trans("eseom-order-form.errorModal.errorMsg2", [])
                ]
            );
        }

        foreach ($foundProducts as $foundProduct) {
            /* BEGIN - CHECK WHETHER THE PRODUCT IS A MAIN PRODUCT AND HAS CHILDREN/VARIANTS - ONLY THE VARIANTS/CHILDS CAN BE ADDED TO THE CART */
            if ($foundProduct->getParentId() === null && $foundProduct->getChildCount() > 0) {
                return new JsonResponse(
                    [
                        'success' => 0,
                        'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg7", ['%productNumber%' => $foundProduct->getProductNumber()])
                    ]
                );
            }
            /* END - CHECK WHETHER THE PRODUCT IS A MAIN PRODUCT AND HAS CHILDREN/VARIANTS - ONLY THE VARIANTS/CHILDS CAN BE ADDED TO THE CART */

            /* BEGIN - GET THE VARIATION OPTIONS */
            foreach ($foundProduct->getOptions() as $productVariationOption) {
                $productVariationOptions[] = $productVariationOption->getGroup()->getTranslated()['name'] . ': ' . $productVariationOption->getTranslated()['name'];
            }
            /* END - GET THE VARIATION OPTIONS */

            /* BEGIN - GET THE PRODUCT PROPERTIES */
            foreach ($foundProduct->getProperties() as $productProperty) {
                $productProperties[] = $productProperty->getGroup()->getTranslated()['name'] . ': ' . $productProperty->getTranslated()['name'];
            }
            /* END - GET THE PRODUCT PROPERTIES */

            $productName = $foundProduct->getTranslated()['name'];
            if ($foundProduct->getCover() !== null) {
                $productCoverImageId = $foundProduct->getCover()->getMediaId();

                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('id', $productCoverImageId));
                $criteria->setLimit(1);

                $foundCoverImages = $this->mediaRepository->search(
                    $criteria, $context->getContext()
                );

                foreach ($foundCoverImages as $foundCoverImage) {
                    $productCoverImageUrl = $foundCoverImage->getUrl();
                }
            }

            $productPriceAndDeliveryInformationArr = $this->determineLineItemPriceAndDeliveryInformation($cart, $foundProduct, $productQuantity, $context);
            $productPrice = $productPriceAndDeliveryInformationArr['productPrice'];
            $productDeliveryInformation = $productPriceAndDeliveryInformationArr['productDeliveryInformation'];
            $productRestockTime = $productPriceAndDeliveryInformationArr['productRestockTime'];

            $productAvailableStock = $foundProduct->getAvailableStock();
            if($showProductStockInModal){
                $productAvailableStockTxt = $this->translator->trans("eseom-order-form.infoModal.availableStock", []) . " " . $productAvailableStock;
            }

            if ($foundProduct->getTranslated()['description'] !== null) {
                $productDescription = (strlen($foundProduct->getTranslated()['description']) > 250) ? (mb_convert_encoding(substr(strip_tags($foundProduct->getTranslated()['description'], '<br>'), 0, 610), 'UTF-8', 'UTF-8') . '...') : $foundProduct->getTranslated()['description'];
            }

            if ($foundProduct->getEan() !== null) {
                $productEAN = $this->translator->trans("eseom-order-form.infoModal.ean", []) . ' ' . $foundProduct->getEan();
            }

            foreach ($context->getSalesChannel()->getDomains() as $productSalesChannelDomains) {
                if ($productSalesChannelDomains->getSalesChannelId() === $context->getSalesChannel()->getId() && $productSalesChannelDomains->getLanguageId() === $context->getSalesChannel()->getLanguageId()) {
                    $productBaseUrl = $productSalesChannelDomains->getUrl();
                    break;
                }
            }

            $productSeoUrls = $foundProduct->getSeoUrls();
            foreach ($productSeoUrls as $productSeoUrl) {
                if ($context->getSalesChannel()->getId() === $productSeoUrl->getSalesChannelId() && $context->getSalesChannel()->getLanguageId() === $productSeoUrl->getLanguageId()) {
                    $productUrl = $productBaseUrl . "/" . $productSeoUrl->getSeoPathInfo();
                    break;
                }
            }
        }

        return new JsonResponse(
            [
                'success' => 1,
                'msg' => 'Request successful',
                'productName' => $productName,
                'productCoverImageUrl' => $productCoverImageUrl,
                'productPrice' => number_format(round($productPrice, 2), 2, ",", "."),
                'productDeliveryInformation' => $productDeliveryInformation,
                'productRestockTime' => $productRestockTime,
                'productPriceCurrency' => $context->getCurrency()->getSymbol(),
                'productAvailableStock' => $productAvailableStock,
                'productAvailableStockTxt' => $productAvailableStockTxt,
                'productVariationOptions' => $productVariationOptions,
                'productVariationOptionsTxt' => $this->translator->trans("eseom-order-form.infoModal.productVariationOptions", []) . " " . implode(', ', $productVariationOptions),
                'productProperties' => $productProperties,
                'productPropertiesTxt' => $this->translator->trans("eseom-order-form.infoModal.productProperties", []) . " " . implode(', ', $productProperties),
                'productDescription' => $productDescription,
                'productEAN' => $productEAN,
                'productUrl' => $productUrl,
                'productUrlLinkText' => $this->translator->trans("eseom-order-form.infoModal.productUrlLinkText", [])
            ]
        );
    }

    #[Route(path:"/eseom-order-form/get-product-price", name:"frontend.eseomorderform.getproductprice", options:["seo"=>"false"], methods:["POST"], defaults:["XmlHttpRequest"=>true])]

    public function orderFormGetProductPrice(Request $request, Cart $cart, SalesChannelContext $context): JsonResponse {
        $configFields = $this->systemConfig->get('EseomOrderForm.config', $context->getSalesChannelId());
        $productNumber = trim($request->get('productNumber'));
        $productQuantity = intval($request->get('productQuantity'));
        $showSearchSuggestions = boolval($request->get('showSearchSuggestions'));
        $navigationId = trim($request->get('navigationId'));

        $productName = "";
        $productPrice = "-";
        $productDeliveryInformation = "-";
        $productRestockTime = "-";

        if (empty($productNumber)) {
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg1", []),
                    'foundProduct' => [],
                    'foundProductSuggestions' => []
                ]
            );
        }

        /* BEGIN - SEARCH AFTER PRODUCT WITH PRODUCTNUMBER or NAME */
        $criteria = new Criteria();
        /* BEGIN - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        if (isset($configFields) && sizeof($configFields) > 0) {
            $twoStepSearch = false;
            $findOnlyCategoryProducts = false;
            foreach ($configFields as $key => $value) {
                if ($key === 'eseomOrderFormExcludeProducts') {
                    if (!empty($value)) {
                        $criteria->addFilter(new NotFilter('AND', [new EqualsAnyFilter('id', $value)]));
                    }
                }
                if ($key === 'eseomOrderFormOnlyCategoryProducts' && $value === true) {
                    $findOnlyCategoryProducts = true;
                }

                if ($key === 'eseomOrderFormTwoStepSearch' && $value === true) {
//                    $criteria->addFilter(new EqualsFilter('parentId', null));
                    $criteria->addFilter(new OrFilter([
                        new EqualsFilter('parentId', null),
                        new EqualsFilter('productNumber', $productNumber),
                        new EqualsFilter('name', $productNumber)
                    ]));
                    $twoStepSearch = true;
                }
            }
        }

        if ($twoStepSearch === false) {
            $criteria->addFilter(new OrFilter([
                new EqualsFilter('childCount', 0),
                new EqualsFilter('childCount', null),
            ]));
        }

        $criteria->addAssociation('options');
        $criteria->addAssociation('options.group');

        /* Use only products from category where order form is installed. Will not work if order-form is installed on a landing page */
        if ($findOnlyCategoryProducts) {
            $criteria->addFilter(new EqualsFilter('product.categories.id', $navigationId));
        }

        /* END - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */

        /* $criteria->addFilter(new EqualsFilter('productNumber', $productNumber)); */
        if (isset($configFields) && sizeof($configFields) > 0 && $configFields['eseomOrderFormShowEanBelowDescription']) {
            $criteria->addFilter(new OrFilter([
                new ContainsFilter('productNumber', $productNumber),
                new ContainsFilter('name', $productNumber),
                new ContainsFilter('ean', $productNumber)
            ]));
        }
        else {
            $criteria->addFilter(new OrFilter([
                new ContainsFilter('productNumber', $productNumber),
                new ContainsFilter('name', $productNumber)
            ]));
        }

        $criteria->setLimit(20);

        $foundProducts = $this->salesChannelProductRepository->search(
            $criteria, $context
        );
        /* END - SEARCH AFTER PRODUCT WITH PRODUCTNUMBER or NAME */

        if (sizeof($foundProducts) === 0) {
            /*
             * $this->container
              ->get('translator')
              ->trans($snippet, $parameters)
             */
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg2", []),
                    'foundProduct' => [],
                    'foundProductSuggestions' => []
                ]
            );
        }

        $foundProductResult = [];
        $foundProductSuggestions = [];
        $shopCartSettingMaxPurchase = $this->systemConfig->getInt(
            'core.cart.maxQuantity',
            $context->getSalesChannelId()
        );

        if (sizeof($foundProducts) > 0) {
            foreach ($foundProducts as $foundProduct) {
                if (
                    strtolower($foundProduct->getProductNumber()) === strtolower($productNumber) ||
                    strtolower($foundProduct->getTranslated()['name']) === strtolower($productNumber) ||
                    (isset($configFields) && sizeof($configFields) > 0 && $configFields['eseomOrderFormShowEanBelowDescription'] && $foundProduct->getEan() === $productNumber)
                ) {
                    $productName = $foundProduct->getTranslated()['name'];
                    $productEAN = "";
                    if ($foundProduct->getEan() !== null) {
                        $productEAN = $this->translator->trans("eseom-order-form.infoModal.ean", []) . ' ' . $foundProduct->getEan();
                    }

                    if ($foundProduct->getCover() !== null) {
                        $productCoverImageId = $foundProduct->getCover()->getMediaId();

                        $criteria = new Criteria();
                        $criteria->addFilter(new EqualsFilter('id', $productCoverImageId));
                        $criteria->setLimit(1);

                        $foundCoverImages = $this->mediaRepository->search(
                            $criteria, $context->getContext()
                        );

                        foreach ($foundCoverImages as $foundCoverImage) {
                            $productCoverImageUrl = $foundCoverImage->getUrl();
                        }
                    } else {
                        $productCoverImageUrl = '';
                    }

                    $productAvailableStock = $foundProduct->getAvailableStock();
                    $productPurchaseSteps = $foundProduct->getPurchaseSteps() ? $foundProduct->getPurchaseSteps() : 1;
                    $productMaxPurchase = $foundProduct->getMaxPurchase() ? $foundProduct->getMaxPurchase() : $shopCartSettingMaxPurchase;
                    $productMinPurchase = $foundProduct->getMinPurchase() ? $foundProduct->getMinPurchase() : 1;
                    $productIsCloseOut = $foundProduct->getIsCloseOut();

                    /* BEGIN - CHECK WHETHER THE PRODUCT IS A MAIN PRODUCT AND HAS CHILDREN/VARIANTS - ONLY THE VARIANTS/CHILDS CAN BE ADDED TO THE CART */
                    if ($foundProduct->getParentId() === null && $foundProduct->getChildCount() > 0) {
                        $foundProductResult = [
                            'productSku' => $foundProduct->getProductNumber(),
                            'productAvailableStock' => $productAvailableStock,
                            'productName' => $productName,
                            'productCoverImageUrl' => $productCoverImageUrl,
                            'isMainProductWithVariants' => 1,
                            'productErrorTxt' => $this->translator->trans("eseom-order-form.errorModal.errorMsg17", [])
                        ];

                        return new JsonResponse(
                            [
                                'success' => 0,
                                'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg7", ['%productNumber%' => $foundProduct->getProductNumber()]),
                                'foundProduct' => $foundProductResult,
                                'foundProductSuggestions' => []
                            ]
                        );
                    }
                    /* END - CHECK WHETHER THE PRODUCT IS A MAIN PRODUCT AND HAS CHILDREN/VARIANTS - ONLY THE VARIANTS/CHILDS CAN BE ADDED TO THE CART */

                    $isValidProductQuantity = $this->isValidProductQuantity($foundProduct, $productQuantity, $cart, $context);
                    $isValidProductQuantityDecoded = json_decode($isValidProductQuantity->getContent(), true);
                    if ($isValidProductQuantityDecoded['success'] === 0) {
                        return $isValidProductQuantity;
                    }

                    $productPriceAndDeliveryInformationArr = $this->determineLineItemPriceAndDeliveryInformation($cart,
                        $foundProduct, $productQuantity, $context);
                    $productPrice = $productPriceAndDeliveryInformationArr['productPrice'];
                    $productDeliveryInformation = $productPriceAndDeliveryInformationArr['productDeliveryInformation'];
                    $productRestockTime = $productPriceAndDeliveryInformationArr['productRestockTime'];

                    $productDeliveryTxt = $productPriceAndDeliveryInformationArr['productDeliveryInformation'];
                    if (!$foundProduct->getIsCloseout()) {
                        if ($productDeliveryInformation !== "-" && $productRestockTime !== "-") {
                            $productDeliveryTxt = $this->translator->trans("eseom-order-form.successModal.successMsg2", [
                                '%productDeliveryInformation%' => $productDeliveryInformation,
                                '%productRestockTime%' => $productRestockTime
                            ]);
                        }
                    }

                    $foundProductResult = [
                        'productSku' => $foundProduct->getProductNumber(),
                        'productName' => $productName,
                        'productEAN' => $productEAN,
                        'productCoverImageUrl' => $productCoverImageUrl,
                        'productAvailableStock' => $productAvailableStock,
                        'productPurchaseSteps' => $productPurchaseSteps,
                        'productMaxPurchase' => $productMaxPurchase,
                        'productMinPurchase' => $productMinPurchase,
                        'productPrice' => round($productPrice, 2),
                        'productPriceFormatted' => number_format(round($productPrice, 2), 2, ",", "."),
                        'productTotalPriceFormatted' => number_format(round($productPrice * $productQuantity, 2), 2, ",", "."),
                        'productPriceCurrency' => $context->getCurrency()->getSymbol(),
                        'productDeliveryInformation' => $productDeliveryInformation,
                        'productRestockTime' => $productRestockTime,
                        'productDeliveryTxt' => $productDeliveryTxt,
                        'productIsCloseOut' => $productIsCloseOut
                    ];
                }

                /* BEGIN - ADD PRODUCT SUGGESTIONS */
                if ($showSearchSuggestions) {
                    $product = [];
                    $product['name'] = $foundProduct->getTranslated()['name'];
                    $product['ean'] = "";
                    if ($foundProduct->getEan() && $foundProduct->getEan() !== "") {
                        $product['ean'] = $this->translator->trans("eseom-order-form.infoModal.ean",[]) . ' ' . $foundProduct->getEan();
                    }
                    $product['number'] = $foundProduct->getProductNumber();

                    $productVariationOptions = [];
                    foreach ($foundProduct->getOptions() as $productVariationOption) {
                        $productVariationOptions[] = $productVariationOption->getGroup()->getTranslated()['name'] . ': ' . $productVariationOption->getTranslated()['name'];
                    }
                    $product['productVariationOptions'] = $productVariationOptions;
                    $product['productVariationOptionsTxt'] = $this->translator->trans("eseom-order-form.infoModal.productVariationOptions", []) . " " . implode(', ', $productVariationOptions);

                    if ($foundProduct->getCover() !== null) {
                        $productCoverImageId = $foundProduct->getCover()->getMediaId();

                        $criteria = new Criteria();
                        $criteria->addFilter(new EqualsFilter('id', $productCoverImageId));
                        $criteria->setLimit(1);

                        $foundCoverImages = $this->mediaRepository->search(
                            $criteria, $context->getContext()
                        );

                        foreach ($foundCoverImages as $foundCoverImage) {
                            $product['productCoverImageUrl'] = $foundCoverImage->getUrl();
                        }
                    } else {
                        $product['productCoverImageUrl'] = '';
                    }
                    $foundProductSuggestions[] = $product;
                }
                /* END - ADD PRODUCT SUGGESTIONS */
            }
        }

        return new JsonResponse(
            [
                'success' => 1,
                'msg' => 'Request successful',
                'foundProduct' => $foundProductResult,
                'foundProductSuggestions' => $foundProductSuggestions
            ]
        );
    }

    /*
     * BEGIN - ADD PRODUCT TO CART TO DETERMINE THE PRICE - REMOVE IT AFTER THE PRICE HAS BEEN FETCHED
     * SO WE ARE ABLE TO DETERMINE THE CORRECT PRICE IF LIST PRICES OR OTHER RULES ARE ACTIVE
     */

    private function determineLineItemPriceAndDeliveryInformation(Cart $cart, ProductEntity $product, int $productQuantity, SalesChannelContext $context): array {
        $lineItemInCart = $this->getLineItemInCart($cart, $product->getId());
        $lineItemAlreadyInCart = false;
        $lineItemsToCartCollection = new LineItemCollection();
        $addedToCart = true;

        if ($lineItemInCart !== null) {
            $this->cartService->changeQuantity($cart, $lineItemInCart['id'], $lineItemInCart['productQuantity'] + $productQuantity, $context);
            $lineItemAlreadyInCart = true;
        } else {
            $lineItem = $this->factory->create([
                'type' => LineItem::PRODUCT_LINE_ITEM_TYPE, // Results in 'product'
                'referencedId' => $product->getId(),
                'quantity' => $productQuantity/* ,
                      'payload' => ['key' => 'value'] */
            ], $context);

            $lineItemsToCartCollection->add($lineItem);
            $cart->addLineItems($lineItemsToCartCollection);
            //$this->cartService->add($cart, $lineItem, $context);
        }
        $cart = $this->cartService->recalculate($cart, $context);
        if (count($cart->getErrors()) > 0) {
            $addedToCart = false;
        }

        $lineItemInCart = $this->getLineItemInCart($cart, $product->getId());
        $productPrice = $lineItemInCart['price'];
        $productDeliveryInformation = $lineItemInCart['productDeliveryInformation'];
        $productRestockTime = $lineItemInCart['productRestockTime'];

        if ($addedToCart) {
            if ($lineItemAlreadyInCart) {
                $this->cartService->changeQuantity($cart, $lineItemInCart['id'], $lineItemInCart['productQuantity'] - $productQuantity, $context);
            } else {
                $cart->remove($lineItemInCart['id']);
            }
            //$this->cartService->remove($cart, $lineItemInCart['id'], $context);
            $cart = $this->cartService->recalculate($cart, $context);
        }

        $responseArr = [
            'productPrice' => $productPrice,
            'productDeliveryInformation' => $productDeliveryInformation,
            'productRestockTime' => $productRestockTime
        ];

        return $responseArr;
    }

    /*
     * END - ADD PRODUCT TO CART TO DETERMINE THE PRICE - REMOVE IT AFTER THE PRICE HAS BEEN FETCHED
     * SO WE ARE ABLE TO DETERMINE THE CORRECT PRICE IF LIST PRICES OR OTHER RULES ARE ACTIVE
     */

    private function isValidProductQuantity(ProductEntity $product, int $productQuantity, Cart $cart, SalesChannelContext $context): JsonResponse {
        $productName = $product->getTranslated()['name'];
        $productEAN = "";
        if ($product->getEan() !== null) {
            $productEAN = $this->translator->trans("eseom-order-form.infoModal.ean", []) . ' ' . $product->getEan();
        }
        $availableStock = $product->getAvailableStock();
        $productPurchaseSteps = $product->getPurchaseSteps() ? $product->getPurchaseSteps() : 1;
        $shopCartSettingMaxPurchase = $this->systemConfig->getInt(
            'core.cart.maxQuantity',
            $context->getSalesChannelId()
        );
        $productMaxPurchase = $product->getMaxPurchase() ? $product->getMaxPurchase() : $shopCartSettingMaxPurchase;
        $productMinPurchase = $product->getMinPurchase() ? $product->getMinPurchase() : 1;
        $qtyWithCartQty = $productQuantity;
        $lineItemInCartQty = 0;
        $lineItemInCart = $this->getLineItemInCart($cart, $product->getId());

        if ($lineItemInCart !== null) {
            $qtyWithCartQty += $lineItemInCart['productQuantity'];
            $lineItemInCartQty = $lineItemInCart['productQuantity'];
        }

        if ($product->getIsCloseout()) {
            if ($qtyWithCartQty > $availableStock) {
                $foundProductResult = [
                    'productSku' => $product->getProductNumber(),
                    'productName' => $productName,
                    'productEAN' => $productEAN,
                    'productAvailableStock' => $availableStock,
                    'productPurchaseSteps' => $productPurchaseSteps,
                    'productMaxPurchase' => $productMaxPurchase,
                    'productMinPurchase' => $productMinPurchase,
                    'productIsCloseOut' => $product->getIsCloseOut(),
                    'productQuantityInCart' => $lineItemInCartQty,
                    'productErrorTxt' => $this->translator->trans("eseom-order-form.errorModal.errorMsg10", ['%qtyStockAlreadyInCart%' => $lineItemInCartQty])
                ];

                return new JsonResponse(
                    [
                        'success' => 0,
                        'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg8", ['%productNumber%' => $product->getProductNumber(), '%availableStock%' => $product->getAvailableStock()]),
                        'foundProduct' => $foundProductResult
                    ]
                );
            }
        }

        if ($productMaxPurchase !== "" && $qtyWithCartQty > $productMaxPurchase) {
            $foundProductResult = [
                'productSku' => $product->getProductNumber(),
                'productName' => $productName,
                'productEAN' => $productEAN,
                'productAvailableStock' => $availableStock,
                'productPurchaseSteps' => $productPurchaseSteps,
                'productMaxPurchase' => $productMaxPurchase,
                'productMinPurchase' => $productMinPurchase,
                'productIsCloseOut' => $product->getIsCloseOut(),
                'productQuantityInCart' => $lineItemInCartQty,
                'productErrorTxt' => $this->translator->trans("eseom-order-form.errorModal.errorMsg16", ['%qtyStockAlreadyInCart%' => $lineItemInCartQty, '%productMaxPurchase%' => $productMaxPurchase])
            ];

            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg15", ['%productNumber%' => $product->getProductNumber(), '%availableStock%' => $availableStock, '%qtyStockAlreadyInCart%' => $lineItemInCartQty, '%productMaxPurchase%' => $productMaxPurchase]),
                    'foundProduct' => $foundProductResult
                ]
            );
        } else if ($qtyWithCartQty < $productMinPurchase) {
            $foundProductResult = [
                'productSku' => $product->getProductNumber(),
                'productName' => $productName,
                'productEAN' => $productEAN,
                'productAvailableStock' => $availableStock,
                'productPurchaseSteps' => $productPurchaseSteps,
                'productMaxPurchase' => $productMaxPurchase,
                'productMinPurchase' => $productMinPurchase,
                'productIsCloseOut' => $product->getIsCloseOut(),
                'productQuantityInCart' => $lineItemInCartQty,
                'productErrorTxt' => $this->translator->trans("eseom-order-form.errorModal.errorMsg12", ['%qtyStockAlreadyInCart%' => $lineItemInCartQty, '%productMinPurchase%' => $productMinPurchase])
            ];

            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg11", ['%productNumber%' => $product->getProductNumber(), '%productMinPurchase%' => $productMinPurchase]),
                    'foundProduct' => $foundProductResult
                ]
            );
        }


        /* BEGIN - CHECK WHETHER THE PRODUCT'S QUANTITY IS IN THE ALLOWED QUANTITY INCREMENT STEPS */
        if (($qtyWithCartQty - $productMinPurchase) % $productPurchaseSteps !== 0 && $qtyWithCartQty !== $productMinPurchase) {
            $foundProductResult = [
                'productSku' => $product->getProductNumber(),
                'productName' => $productName,
                'productEAN' => $productEAN,
                'productAvailableStock' => $availableStock,
                'productPurchaseSteps' => $productPurchaseSteps,
                'productMaxPurchase' => $productMaxPurchase,
                'productMinPurchase' => $productMinPurchase,
                'productIsCloseOut' => $product->getIsCloseOut(),
                'productQuantityInCart' => $lineItemInCartQty,
                'productErrorTxt' => $this->translator->trans("eseom-order-form.errorModal.errorMsg14", ['%productNumber%' => $product->getProductNumber(), '%productPurchaseSteps%' => $productPurchaseSteps, '%productMinPurchase%' => $productMinPurchase])
            ];

            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg13", ['%productNumber%' => $product->getProductNumber(), '%productPurchaseSteps%' => $productPurchaseSteps]),
                    'foundProduct' => $foundProductResult
                ]
            );
        }
        /* END - CHECK WHETHER THE PRODUCT'S QUANTITY IS IN THE ALLOWED QUANTITY INCREMENT STEPS */

        return new JsonResponse(
            [
                'success' => 1
            ]
        );
    }

    #[Route(path:"/eseom-order-form/get-product-variations", name:"frontend.eseomorderform.getproductvariations", options:["seo"=>"false"], methods:["POST"], defaults:["XmlHttpRequest"=>true])]

    public function orderFormGetProductVariations(Request $request, Cart $cart, SalesChannelContext $context): JsonResponse {
        $configFields = $this->systemConfig->get('EseomOrderForm.config', $context->getSalesChannelId());
        $productNumber = trim($request->get('productNumber'));
        $productBaseUrl = "";

        $variationHtmlTable = "";

        if (empty($productNumber)) {
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg18", [])
                ]
            );
        }

        /* BEGIN - SEARCH AFTER PRODUCT WITH PRODUCTNUMBER */
        $criteria = new Criteria();
        /* BEGIN - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        if (isset($configFields) && sizeof($configFields) > 0) {
            foreach ($configFields as $key => $value) {
                if ($key === 'eseomOrderFormExcludeProducts') {
                    if (!empty($value)) {
                        $criteria->addFilter(new NotFilter('AND', [new EqualsAnyFilter('id', $value)]));
                    }
                }
            }
        }
        /* END - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        $criteria->setLimit(1);

        $foundProducts = $this->salesChannelProductRepository->search(
            $criteria, $context
        );

        if (sizeof($foundProducts) === 0) {
            /*
             * $this->container
              ->get('translator')
              ->trans($snippet, $parameters)
             */
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg2", [])
                ]
            );
        }

        /* BEGIN - SEARCH AFTER VARIANTS OF THE PRODUCT */
        $criteria2 = new Criteria();
        /* BEGIN - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        if (isset($configFields) && sizeof($configFields) > 0) {
            foreach ($configFields as $key => $value) {
                if ($key === 'eseomOrderFormExcludeProducts') {
                    if (!empty($value)) {
                        $criteria2->addFilter(new NotFilter('AND', [new EqualsAnyFilter('id', $value)]));
                    }
                }
            }
        }
        /* END - ADD THE PRODUCTS THAT SHOULD BE EXCLUDED - DEFINED IN THE PLUGIN CONFIGURATION */
        $criteria2->addFilter(new EqualsFilter('parentId', $foundProducts->first()->getId()));
        $criteria2->addAssociation('cover');
        $criteria2->addAssociation('seoUrls');
        $criteria2->addAssociation('properties');
        $criteria2->addAssociation('properties.group');
        $criteria2->addAssociation('options');
        $criteria2->addAssociation('options.group');

        $foundProducts2 = $this->salesChannelProductRepository->search(
            $criteria2, $context
        );
        /* END - SEARCH AFTER VARIANTS OF THE PRODUCT */

        if (sizeof($foundProducts2) === 0) {
            /*
             * $this->container
              ->get('translator')
              ->trans($snippet, $parameters)
             */
            return new JsonResponse(
                [
                    'success' => 0,
                    'msg' => $this->translator->trans("eseom-order-form.errorModal.errorMsg19", [])
                ]
            );
        }

        $variationHtmlTable .= '<table class="table table-responsive">';
        $variationHtmlTable .= '<thead>';
        $variationHtmlTable .= '<tr>';
        $variationHtmlTable .= '<th class="align-middle">' . $this->translator->trans("eseom-order-form.chooseVariantModal.thead.productImage", []) . '</th>';
        $variationHtmlTable .= '<th class="align-middle">' . $this->translator->trans("eseom-order-form.chooseVariantModal.thead.productName", []) . '</th>';
        $variationHtmlTable .= '<th class="align-middle">' . $this->translator->trans("eseom-order-form.chooseVariantModal.thead.options", []) . '</th>';
        $variationHtmlTable .= '<th class="align-middle">' . $this->translator->trans("eseom-order-form.chooseVariantModal.thead.properties", []) . '</th>';
        $variationHtmlTable .= '<th class="align-middle">' . $this->translator->trans("eseom-order-form.chooseVariantModal.thead.actions", []) . '</th>';
        $variationHtmlTable .= '</tr>';
        $variationHtmlTable .= '</thead>';
        $variationHtmlTable .= '<tbody>';

        foreach ($context->getSalesChannel()->getDomains() as $productSalesChannelDomains) {
            if ($productSalesChannelDomains->getSalesChannelId() === $context->getSalesChannel()->getId() && $productSalesChannelDomains->getLanguageId() === $context->getSalesChannel()->getLanguageId()) {
                $productBaseUrl = $productSalesChannelDomains->getUrl();
                break;
            }
        }

        foreach ($foundProducts2 as $foundProduct2) {
            $productName = $foundProduct2->getTranslated()['name'];
            $productCoverImageUrl = "";
            $productVariationOptions = [];
            $productProperties = [];
            $productUrl = "";

            if ($foundProduct2->getCover() !== null) {
                $productCoverImageId = $foundProduct2->getCover()->getMediaId();

                $criteria = new Criteria();
                $criteria->addFilter(new EqualsFilter('id', $productCoverImageId));
                $criteria->setLimit(1);

                $foundCoverImages = $this->mediaRepository->search(
                    $criteria, $context->getContext()
                );

                foreach ($foundCoverImages as $foundCoverImage) {
                    $productCoverImageUrl = $foundCoverImage->getUrl();
                }
            }

            $productSeoUrls = $foundProduct2->getSeoUrls();

            foreach ($productSeoUrls as $productSeoUrl) {
                if ($context->getSalesChannel()->getId() === $productSeoUrl->getSalesChannelId() && $context->getSalesChannel()->getLanguageId() === $productSeoUrl->getLanguageId()) {
                    $productUrl = $productBaseUrl . "/" . $productSeoUrl->getSeoPathInfo();
                    break;
                }
            }

            /* BEGIN - GET THE VARIATION OPTIONS */
            foreach ($foundProduct2->getOptions() as $productVariationOption) {
                $productVariationOptions[] = $productVariationOption->getGroup()->getTranslated()['name'] . ': ' . $productVariationOption->getTranslated()['name'];
            }
            /* END - GET THE VARIATION OPTIONS */

            /* BEGIN - GET THE PRODUCT PROPERTIES */
            foreach ($foundProduct2->getProperties() as $productProperty) {
                $productProperties[] = $productProperty->getGroup()->getTranslated()['name'] . ': ' . $productProperty->getTranslated()['name'];
            }
            /* END - GET THE PRODUCT PROPERTIES */

            $variationHtmlTable .= '<tr>';
            $variationHtmlTable .= '<td class="align-middle"><img class="img-fluid" src="' . $productCoverImageUrl . '"></td>';
            $productNameAndEAN = $productName;
            if (isset($configFields) && sizeof($configFields) > 0 && $configFields['eseomOrderFormShowEanBelowDescription'] && $foundProduct2->getEan() && $foundProduct2->getEan() !== "") {
                $productNameAndEAN .= '<br><span class="ean-text">' . $this->translator->trans("eseom-order-form.infoModal.ean",[]) . ' ' . $foundProduct2->getEan() . '</span>';
            }
            $variationHtmlTable .= '<td class="align-middle">' . $productNameAndEAN . '</td>';
            $variationHtmlTable .= '<td class="align-middle">' . implode(', ', $productVariationOptions) . '</td>';
            $variationHtmlTable .= '<td class="align-middle">' . implode(', ', $productProperties) . '</td>';
            $variationHtmlTable .= '<td class="align-middle eseom-order-form-choose-variant-action-btns-td"><button type="button" data-variant-order-number="' . $foundProduct2->getProductNumber() . '" class="eseom-order-form-choose-variant-use-btn btn btn-success"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#758CA3" fill-rule="evenodd" d="M24,12 C24,18.627417 18.627417,24 12,24 C5.372583,24 -7.65539184e-17,18.627417 -8.8817842e-16,12 C5.40562444e-15,5.372583 5.372583,1.21743707e-15 12,0 C18.627417,5.58919772e-16 24,5.372583 24,12 Z M12,2 C6.4771525,2 2,6.4771525 2,12 C2,17.5228475 6.4771525,22 12,22 C17.5228475,22 22,17.5228475 22,12 C22,6.4771525 17.5228475,2 12,2 Z M7.70710678,12.2928932 L10,14.5857864 L16.2928932,8.29289322 C16.6834175,7.90236893 17.3165825,7.90236893 17.7071068,8.29289322 C18.0976311,8.68341751 18.0976311,9.31658249 17.7071068,9.70710678 L10.7071068,16.7071068 C10.3165825,17.0976311 9.68341751,17.0976311 9.29289322,16.7071068 L6.29289322,13.7071068 C5.90236893,13.3165825 5.90236893,12.6834175 6.29289322,12.2928932 C6.68341751,11.9023689 7.31658249,11.9023689 7.70710678,12.2928932 Z"></path></svg></button><a href="' . $productUrl . '" target="_blank" class="eseom-order-form-choose-variant-product-detail-btn btn btn-info"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="#fff" fill-rule="evenodd" d="M12,7 C12.5522847,7 13,7.44771525 13,8 C13,8.55228475 12.5522847,9 12,9 C11.4477153,9 11,8.55228475 11,8 C11,7.44771525 11.4477153,7 12,7 Z M13,16 C13,16.5522847 12.5522847,17 12,17 C11.4477153,17 11,16.5522847 11,16 L11,11 C11,10.4477153 11.4477153,10 12,10 C12.5522847,10 13,10.4477153 13,11 L13,16 Z M24,12 C24,18.627417 18.627417,24 12,24 C5.372583,24 6.14069502e-15,18.627417 5.32907052e-15,12 C-8.11624501e-16,5.372583 5.372583,4.77015075e-15 12,3.55271368e-15 C18.627417,5.58919772e-16 24,5.372583 24,12 Z M12,2 C6.4771525,2 2,6.4771525 2,12 C2,17.5228475 6.4771525,22 12,22 C17.5228475,22 22,17.5228475 22,12 C22,6.4771525 17.5228475,2 12,2 Z"></path></svg></a></td>';
            $variationHtmlTable .= '</tr>';
            $variationHtmlTable .= '</thead>';
        }
        $variationHtmlTable .= '</tbody>';
        $variationHtmlTable .= '</table>';

        return new JsonResponse(
            [
                'success' => 1,
                'msg' => $this->translator->trans("eseom-order-form.chooseVariantModal.successMsg1", []),
                'variationHtmlTable' => $variationHtmlTable
            ]
        );
    }
}
<?php

declare(strict_types=1);

namespace Eseom\OrderForm\Storefront\TwigExtensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Adapter\Translation\Translator;

class OrderFormProductTwigExtension extends AbstractExtension {

    /**
     * @var SalesChannelRepository
     */
    private $saleChannelProductRepository;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(
            SalesChannelRepository $saleChannelProductRepository,
            Translator $translator
    ) {
        $this->saleChannelProductRepository = $saleChannelProductRepository;
        $this->translator = $translator;
    }

    public function getFunctions() {
        return [
            new TwigFunction('eseomGetProductSkusByIds', [$this, 'getProductSkusByIds']),
        ];
    }

    public function getProductSkusByIds(?array $productIds, SalesChannelContext $context): Array {
        if (empty($productIds)) {
            return array(
                'success' => 0,
                'msg' => $this->translator->trans("eseom-order-form.twigExtensions.errorMsg1", [])
            );
        }

        $parentResponseProductsArr = [];
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('id', $productIds));

        $foundProducts = $this->saleChannelProductRepository->search(
                $criteria, $context
        );

        foreach ($foundProducts as $foundProduct) {
            $responseProducts = [
                'sku' => $foundProduct->getProductNumber()
            ];

            $parentResponseProductsArr[] = $responseProducts;
        }

        if (sizeof($parentResponseProductsArr) > 0) {
            return array(
                'success' => 1,
                'productSkusArr' => $parentResponseProductsArr,
                'msg' => $this->translator->trans("eseom-order-form.twigExtensions.successMsg1", [])
            );
        }

        return array(
            'success' => 0,
            'msg' => $this->translator->trans("eseom-order-form.twigExtensions.errorMsg1", [])
        );
    }

}

<?php declare(strict_types=1);

namespace Shopware\Commercial\B2B\QuickOrder\SalesChannel\Account;

use Shopware\Commercial\B2B\QuickOrder\Domain\CustomerSpecificFeature\CustomerSpecificFeatureService;
use Shopware\Commercial\B2B\QuickOrder\Domain\File\CsvDeleter;
use Shopware\Commercial\B2B\QuickOrder\Domain\File\CsvReader;
use Shopware\Commercial\B2B\QuickOrder\Exception\AccountQuickOrderException;
use Shopware\Commercial\B2B\QuickOrder\Exception\CustomerSpecificFeatureException;
use Shopware\Commercial\B2B\QuickOrder\QuickOrder;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductListRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('checkout')]
class QuickOrderProcessFileRoute extends AbstractQuickOrderProcessFileRoute
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractProductListRoute $productListRoute,
        private readonly CustomerSpecificFeatureService $customerSpecificFeatureService,
        private readonly CsvReader $csvReader,
        private readonly CsvDeleter $csvDeleter,
        private readonly string $maxFileSize,
        private readonly string $supportedFileType,
    ) {
    }

    public function getDecorated(): AbstractQuickOrderProcessFileRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/quick-order/load-file',
        name: 'store-api.quick-order.load-file',
        defaults: ['_entity' => 'quick-order'],
        methods: ['POST'],
        condition: 'service(\'license\').check(\'QUICK_ORDER-9771104\')'
    )]
    public function load(Request $request, SalesChannelContext $context): JsonResponse
    {
        if (!$this->customerSpecificFeatureService->isAllowed($context->getCustomerId(), QuickOrder::CODE)) {
            throw CustomerSpecificFeatureException::notAllowed(QuickOrder::CODE);
        }

        $file = $request->files->get('file');

        if (!$file instanceof UploadedFile) {
            throw AccountQuickOrderException::missingFile();
        }

        $maxSizeInBytes = (int) $this->maxFileSize * 1024 * 1024;

        if ($file->getSize() > $maxSizeInBytes) {
            throw AccountQuickOrderException::overSizeError();
        }

        if ($file->getClientOriginalExtension() !== $this->supportedFileType) {
            throw AccountQuickOrderException::extensionError($file->getClientOriginalExtension());
        }

        $requestedProducts = $this->csvReader->read($file);

        $requestedProductNumbers = array_keys($requestedProducts);

        $criteria = new Criteria();
        $criteria->setTitle('product-detail-quick-order');
        $criteria->addFilter(new EqualsAnyFilter('productNumber', $requestedProductNumbers));
        $criteria->addAssociation('options.group');
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_NONE);

        $products = $this->productListRoute->load($criteria, $context)->getProducts();

        $filteredProducts = [];
        $productsNumber = [];

        /** @var SalesChannelProductEntity $product */
        foreach ($products as $product) {
            $productsNumber[] = $product->getProductNumber();

            $filteredProducts[] = [
                'id' => $product->getId(),
                'productNumber' => $product->getProductNumber(),
                'name' => $product->getTranslation('name'),
                'variation' => $product->getVariation(),
                'minPurchase' => $product->getMinPurchase(),
                'maxPurchase' => $product->getMaxPurchase(),
                'purchaseSteps' => $product->getPurchaseSteps(),
                'calculatedMaxPurchase' => $product->getCalculatedMaxPurchase(),
                'stock' => $product->getStock(),
                'quantity' => $requestedProducts[$product->getProductNumber()],
                'childCount' => $product->getChildCount(),
            ];
        }

        $errorProducts = array_values(
            array_diff($requestedProductNumbers, $productsNumber)
        );

        $this->csvDeleter->delete($file);

        return new JsonResponse([
            'products' => $filteredProducts,
            'errorProducts' => $errorProducts,
        ]);
    }
}

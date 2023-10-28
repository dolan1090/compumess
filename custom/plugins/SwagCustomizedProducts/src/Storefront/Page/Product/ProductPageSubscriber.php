<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Storefront\Page\Product;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Swag\CustomizedProducts\Core\Content\Product\ProductWrittenSubscriber;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Storefront\Page\Product\Extensions\EditConfigurationExtension;
use Swag\CustomizedProducts\Storefront\Page\Product\Extensions\ShareConfigurationExtension;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Aggregate\TemplateConfigurationShareEntity;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\Service\TemplateConfigurationService;
use Swag\CustomizedProducts\Template\Aggregate\TemplateConfiguration\TemplateConfigurationEntity;
use Swag\CustomizedProducts\Template\SalesChannel\Price\PriceService;
use Swag\CustomizedProducts\Template\TemplateEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    final public const EDIT_CONFIGURATION_PARAMETER = 'swagCustomizedProductsConfigurationEdit';
    final public const SHARE_CONFIGURATION_PARAMETER = 'swagCustomizedProductsConfigurationShare';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly PriceService $priceService,
        private readonly EntityRepository $configurationRepository,
        private readonly EntityRepository $configurationShareRepository,
        private readonly TranslatorInterface $translator,
        private readonly TemplateConfigurationService $configurationService,
        private readonly EntityRepository $mediaRepository
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => [
                ['addMediaToTemplate', 500],
                ['removeCustomizedProductsTemplateFromNoneInheritedVariant', 400],
                ['enrichOptionPriceAbleDisplayPrices', 300],
                ['addEditConfigurationExtension', 200],
                ['addShareConfigurationExtension', 100],
            ],
        ];
    }

    /**
     * Please bear in mind, that the SalesChannelProductRepository gets decorated here: Swag\CustomizedProducts\Core\Content\Product\SalesChannel\SalesChannelProductRepositoryDecorator
     * And that the SalesChannelTemplateDefinition requires all associations except the media per default here: Swag\CustomizedProducts\Template\SalesChannel\SalesChannelTemplateDefinition::processCriteria
     * For better performance we only load the media once its needed on the product detail page.
     */
    public function addMediaToTemplate(ProductPageLoadedEvent $event): void
    {
        try {
            /** @var TemplateEntity|null $template */
            $template = $event->getPage()
                ->getProduct()
                ->get(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
        } catch (\InvalidArgumentException) {
            return;
        }

        if ($template === null) {
            return;
        }

        $criteria = new Criteria();
        $criteria->setLimit(1);
        $criteria->addFilter(
            new EqualsFilter('swagCustomizedProductsTemplate.id', $template->getId())
        );

        /** @var MediaEntity|null $media */
        $media = $this->mediaRepository->search($criteria, $event->getSalesChannelContext()->getContext())->first();
        if ($media === null) {
            return;
        }

        $template->setMediaId($media->getId());
        $template->setMedia($media);
    }

    public function removeCustomizedProductsTemplateFromNoneInheritedVariant(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        $parentId = $product->getParentId();

        if ($parentId === null) {
            return;
        }

        $customFields = $product->getCustomFields();
        if ($customFields === null
            || !\array_key_exists(ProductWrittenSubscriber::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD, $customFields)
        ) {
            return;
        }

        if ($customFields[ProductWrittenSubscriber::SWAG_CUSTOMIZED_PRODUCTS_TEMPLATE_INHERITED_CUSTOM_FIELD]) {
            return;
        }

        if ($this->variantHasOwnTemplateAssigned($product->getId(), $event->getContext())) {
            return;
        }

        $product->removeExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }

    public function enrichOptionPriceAbleDisplayPrices(ProductPageLoadedEvent $event): void
    {
        /** @var TemplateEntity|null $customizedProductsTemplate */
        $customizedProductsTemplate = $event->getPage()->getProduct()->getExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);

        if ($customizedProductsTemplate === null) {
            return;
        }

        $this->priceService->calculateCurrencyPrices($customizedProductsTemplate, $event->getSalesChannelContext());
    }

    public function addEditConfigurationExtension(ProductPageLoadedEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getRequest()->getSession();

        $query = $event->getRequest()->query;
        if (!$query->has(self::EDIT_CONFIGURATION_PARAMETER)) {
            return;
        }

        $configurationId = $query->getAlnum(self::EDIT_CONFIGURATION_PARAMETER);
        if (!Uuid::isValid($configurationId)) {
            return;
        }

        $context = $event->getContext();
        $configuration = $this->getConfiguration($configurationId, $context);
        if ($configuration === null) {
            $session->getFlashBag()->add(
                'info',
                $this->translator->trans('customizedProducts.configurationEdit.notRestorable')
            );

            return;
        }

        if (!$this->configurationService->isConfigurationFullyRestorable($configuration, $context)) {
            $session->getFlashBag()->add(
                'info',
                $this->translator->trans('customizedProducts.configurationEdit.notFullyRestorable')
            );
        }

        $editConfigurationExtension = (new EditConfigurationExtension())->assign([
            'oldHash' => $configuration->getHash(),
            'configuration' => $configuration->getConfiguration(),
        ]);

        $event->getPage()->addExtension(self::EDIT_CONFIGURATION_PARAMETER, $editConfigurationExtension);
    }

    public function addShareConfigurationExtension(ProductPageLoadedEvent $event): void
    {
        /** @var Session $session */
        $session = $event->getRequest()->getSession();
        $context = $event->getContext();
        $query = $event->getRequest()->query;
        if (!$query->has(self::SHARE_CONFIGURATION_PARAMETER)) {
            return;
        }

        $shareId = $query->getAlnum(self::SHARE_CONFIGURATION_PARAMETER);
        if (!Uuid::isValid($shareId)) {
            return;
        }

        $configurationShare = $this->getShare($shareId, $context);
        if ($configurationShare === null) {
            $session->getFlashBag()->add(
                'info',
                $this->translator->trans('customizedProducts.configurationShare.unavailable')
            );

            return;
        }

        $shareConfigurationExtension = (new ShareConfigurationExtension())->assign([
            'configuration' => $configurationShare->getTemplateConfiguration()->getConfiguration(),
        ]);

        $event->getPage()->addExtension(self::SHARE_CONFIGURATION_PARAMETER, $shareConfigurationExtension);

        if ($configurationShare->isOneTime()) {
            $this->configurationShareRepository->delete([['id' => $configurationShare->getId()]], $context);
        }
    }

    private function variantHasOwnTemplateAssigned(string $id, Context $context): bool
    {
        $considerInheritance = $context->considerInheritance();
        $criteria = new Criteria([$id]);
        $criteria->addAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
        $criteria->setLimit(1);
        $context->setConsiderInheritance(false);

        /** @var ProductEntity|null $product */
        $product = $this->productRepository->search($criteria, $context)->first();
        $context->setConsiderInheritance($considerInheritance);

        if ($product === null) {
            return false;
        }

        return $product->hasExtension(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN);
    }

    private function getConfiguration(string $configurationId, Context $context): ?TemplateConfigurationEntity
    {
        $criteria = new Criteria([$configurationId]);

        /** @var TemplateConfigurationEntity|null $templateConfiguration */
        $templateConfiguration = $this->configurationRepository->search($criteria, $context)->first();

        return $templateConfiguration;
    }

    private function getShare(string $shareId, Context $context): ?TemplateConfigurationShareEntity
    {
        $criteria = new Criteria([$shareId]);
        $criteria->addAssociation('templateConfiguration');

        /** @var TemplateConfigurationShareEntity|null $templateShare */
        $templateShare = $this->configurationShareRepository->search($criteria, $context)->first();

        return $templateShare;
    }
}

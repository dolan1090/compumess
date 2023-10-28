<?php declare(strict_types=1);

namespace Shopware\Commercial\ReturnManagement\Domain\Document;

use Shopware\Commercial\Licensing\Exception\LicenseExpiredException;
use Shopware\Commercial\Licensing\License;
use Shopware\Commercial\ReturnManagement\Domain\StateHandler\PositionStateHandler;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnCollection;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturn\OrderReturnEntity;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemCollection;
use Shopware\Commercial\ReturnManagement\Entity\OrderReturnLineItem\OrderReturnLineItemEntity;
use Shopware\Commercial\ReturnManagement\Event\PartialCancellationOrdersEvent;
use Shopware\Core\Checkout\Cart\Price\AmountCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CartPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Document\Exception\DocumentGenerationException;
use Shopware\Core\Checkout\Document\Renderer\AbstractDocumentRenderer;
use Shopware\Core\Checkout\Document\Renderer\DocumentRendererConfig;
use Shopware\Core\Checkout\Document\Renderer\OrderDocumentCriteriaFactory;
use Shopware\Core\Checkout\Document\Renderer\RenderedDocument;
use Shopware\Core\Checkout\Document\Renderer\RendererResult;
use Shopware\Core\Checkout\Document\Service\DocumentConfigLoader;
use Shopware\Core\Checkout\Document\Service\ReferenceInvoiceLoader;
use Shopware\Core\Checkout\Document\Struct\DocumentGenerateOperation;
use Shopware\Core\Checkout\Document\Twig\DocumentTemplateRenderer;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderCollection;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Exception\InvalidOrderException;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\System\Locale\LocaleEntity;
use Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextRestorer;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @final
 */
#[Package('checkout')]
class PartialCancellationRenderer extends AbstractDocumentRenderer
{
    final public const TYPE = 'partial_cancellation';

    private const FEATURE_TOGGLE_FOR_SERVICE = 'RETURNS_MANAGEMENT-1630550';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $orderRepository,
        private readonly DocumentConfigLoader $documentConfigLoader,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DocumentTemplateRenderer $documentTemplateRenderer,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly ReferenceInvoiceLoader $referenceInvoiceLoader,
        private readonly string $rootDir,
        private readonly SalesChannelContextRestorer $contextRestorer,
        private readonly QuantityPriceCalculator $calculator,
        private readonly AmountCalculator $amountCalculator
    ) {
    }

    public function supports(): string
    {
        return self::TYPE;
    }

    /**
     * @internal
     */
    public function render(array $operations, Context $context, DocumentRendererConfig $rendererConfig): RendererResult
    {
        if (!License::get(self::FEATURE_TOGGLE_FOR_SERVICE)) {
            throw new LicenseExpiredException();
        }

        $ids = \array_map(fn (DocumentGenerateOperation $operation) => $operation->getOrderId(), $operations);

        if (empty($ids)) {
            return new RendererResult();
        }

        $result = new RendererResult();

        $template = '@ReturnManagement/documents/partial_cancellation.html.twig';

        $referenceInvoiceNumbers = [];

        $orders = new OrderCollection();

        /** @var DocumentGenerateOperation $operation */
        foreach ($operations as $operation) {
            try {
                $orderId = $operation->getOrderId();
                $invoice = $this->referenceInvoiceLoader->load($orderId, $operation->getReferencedDocumentId(), $rendererConfig->deepLinkCode);

                if (empty($invoice)) {
                    throw new DocumentGenerationException('Can not generate partial cancellation document because no invoice document exists. OrderId: ' . $operation->getOrderId());
                }

                /** @var string[] $documentRefer */
                $documentRefer = json_decode((string) $invoice['config'], true, 512, \JSON_THROW_ON_ERROR);

                $referenceInvoiceNumbers[$orderId] = $documentRefer['documentNumber'];

                $includeCancelled = isset($operation->getConfig()['custom']) ? $operation->getConfig()['custom']['includeCancelled'] : false;

                $order = $this->getOrder($orderId, $context, $rendererConfig->deepLinkCode, $includeCancelled);

                $orders->add($order);
                $operation->setReferencedDocumentId($invoice['id']);
                if ($order->getVersionId()) {
                    $operation->setOrderVersionId($order->getVersionId());
                }
            } catch (\Throwable $exception) {
                $result->addError($operation->getOrderId(), $exception);
            }
        }

        // TODO: future implementation (only fetch required data and associations)

        $this->eventDispatcher->dispatch(new PartialCancellationOrdersEvent($orders, $context));

        foreach ($orders as $order) {
            $orderId = $order->getId();

            try {
                $operation = $operations[$orderId] ?? null;
                if ($operation === null) {
                    continue;
                }

                $order = $this->handlePrices($order, $context);

                $config = clone $this->documentConfigLoader->load(self::TYPE, $order->getSalesChannelId(), $context);

                $config->merge($operation->getConfig());

                $number = $config->getDocumentNumber() ?: $this->getNumber($context, $order, $operation);

                $referenceDocumentNumber = $referenceInvoiceNumbers[$operation->getOrderId()];

                $now = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);

                $config->merge([
                    'documentDate' => $operation->getConfig()['documentDate'] ?? $now,
                    'documentNumber' => $number,
                    'custom' => [
                        'stornoNumber' => $number,
                        'invoiceNumber' => $referenceDocumentNumber,
                    ],
                ]);

                if ($operation->isStatic()) {
                    $doc = new RenderedDocument('', $number, $config->buildName(), $operation->getFileType(), $config->jsonSerialize());
                    $result->addSuccess($orderId, $doc);

                    continue;
                }

                /** @var LocaleEntity $locale */
                $locale = $order->getLanguage()->getLocale();
                $html = $this->documentTemplateRenderer->render(
                    $template,
                    [
                        'order' => $order,
                        'config' => $config,
                        'rootDir' => $this->rootDir,
                        'context' => $context,
                    ],
                    $context,
                    $order->getSalesChannelId(),
                    $order->getLanguageId(),
                    $locale->getCode()
                );

                $doc = new RenderedDocument(
                    $html,
                    $number,
                    $config->buildName(),
                    $operation->getFileType(),
                    $config->jsonSerialize(),
                );

                $result->addSuccess($orderId, $doc);
            } catch (\Throwable $exception) {
                $result->addError($orderId, $exception);
            }
        }

        return $result;
    }

    public function getDecorated(): AbstractDocumentRenderer
    {
        throw new DecorationPatternException(self::class);
    }

    private function getOrder(string $orderId, Context $context, string $deepLinkCode = '', bool $includeCancelled = false): OrderEntity
    {
        $criteria = OrderDocumentCriteriaFactory::create([$orderId], $deepLinkCode);
        $criteria->addAssociation('returns');

        $criteriaLineItem = $criteria->getAssociation('lineItems');
        $criteriaLineItem->addAssociation('state');
        $criteriaLineItem->addAssociation('returns.state');

        $states = [PositionStateHandler::STATE_RETURN_REQUESTED, PositionStateHandler::STATE_RETURNED, PositionStateHandler::STATE_RETURNED_PARTIALLY];
        if ($includeCancelled) {
            $states[] = PositionStateHandler::STATE_CANCELLED;
        }

        $criteriaLineItem->addFilter(new EqualsAnyFilter('state.technicalName', $states));

        /** @var OrderEntity|null $order */
        $order = $this->orderRepository->search($criteria, $context)->get($orderId);
        if ($order === null) {
            throw new InvalidOrderException($orderId);
        }

        return $order;
    }

    private function handlePrices(OrderEntity $order, Context $context): OrderEntity
    {
        if (!$lineItems = $order->getLineItems()) {
            return $order;
        }

        $salesChannelContext = $this->contextRestorer->restoreByOrder($order->getId(), $context);

        /** @var OrderLineItemEntity $lineItem */
        foreach ($lineItems as $lineItem) {
            if ($lineItem->getExtension('returns') === null) {
                continue;
            }

            /** @var OrderReturnLineItemCollection $returnLineItems */
            $returnLineItems = $lineItem->getExtension('returns');

            if (\count($returnLineItems) === 0) {
                $lineItem->setUnitPrice($lineItem->getUnitPrice() / -1);
                $lineItem->setTotalPrice($lineItem->getTotalPrice() / -1);

                continue;
            }

            if ($lineItem->getPrice() === null) {
                continue;
            }

            $refundAmount = 0;
            $returnQuantity = 0;
            /** @var OrderReturnLineItemEntity $returnLineItem */
            foreach ($returnLineItems as $returnLineItem) {
                $refundAmount += $returnLineItem->getRefundAmount();
                $returnQuantity += $returnLineItem->getQuantity();
            }

            $price = $this->calculator->calculate(
                new QuantityPriceDefinition($refundAmount, $lineItem->getPrice()->getTaxRules()),
                $salesChannelContext
            );

            $lineItem->setPrice($price);
            $lineItem->setUnitPrice($lineItem->getUnitPrice() / -1);
            $lineItem->setTotalPrice($refundAmount / -1);
            $lineItem->setQuantity($returnQuantity);
        }

        $price = $this->amountCalculator->calculate($lineItems->getPrices(), $this->getRefundShippingCosts($order), $salesChannelContext);

        foreach ($price->getCalculatedTaxes()->sortByTax() as $tax) {
            $tax->setTax($tax->getTax() / -1);
        }

        $price = new CartPrice(
            $price->getNetPrice() / -1,
            $price->getTotalPrice() / -1,
            $price->getPositionPrice() / -1,
            $price->getCalculatedTaxes(),
            $price->getTaxRules(),
            $price->getTaxStatus(),
            $price->getRawTotal() / -1,
        );

        $order->setPrice($price);
        $order->setAmountNet($price->getNetPrice());

        return $order;
    }

    private function getNumber(Context $context, OrderEntity $order, DocumentGenerateOperation $operation): string
    {
        return $this->numberRangeValueGenerator->getValue(
            'document_' . self::TYPE,
            $context,
            $order->getSalesChannelId(),
            $operation->isPreview()
        );
    }

    private function getRefundShippingCosts(OrderEntity $order): PriceCollection
    {
        /** @var OrderReturnCollection|null $returns */
        $returns = $order->getExtension('returns');
        if ($returns === null || \count($returns) === 0) {
            return new PriceCollection();
        }

        /** @var OrderReturnEntity $return */
        $return = $returns->first();

        $shippingCost = $return->getShippingCosts();
        if (!$shippingCost || $shippingCost->getTotalPrice() === 0.0) {
            return new PriceCollection();
        }

        $order->setShippingTotal($shippingCost->getTotalPrice() / -1);

        return new PriceCollection([$shippingCost]);
    }
}

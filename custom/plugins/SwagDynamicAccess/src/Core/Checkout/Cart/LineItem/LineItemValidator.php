<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\DynamicAccess\Core\Checkout\Cart\LineItem;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Cart\ProductNotFoundError;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Swag\DynamicAccess\Core\Content\Product\Cart\InvalidProductError;
use Swag\DynamicAccess\DataAbstractionLayer\Extension\CategoryExtension;

class LineItemValidator implements CartValidatorInterface
{
    public const INVALID_PRODUCT = 'swagDynamicAccessInvalidProduct';

    private EntityRepository $productRepository;

    public function __construct(EntityRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
        /** @var array<string, mixed> $referencedIds */
        $referencedIds = [];
        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            $referencedId = $lineItem->getReferencedId();
            $lineItem->removeExtension(self::INVALID_PRODUCT);

            if ($referencedId === null || $lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
                continue;
            }

            if (!Uuid::isValid($referencedId)) {
                $errors->add(new ProductNotFoundError($lineItem->getLabel() ?: $referencedId));
                $lineItem->addExtension(self::INVALID_PRODUCT, new ArrayEntity());

                continue;
            }

            $referencedIds[] = $lineItem->getReferencedId();
        }

        if ($referencedIds === []) {
            return;
        }

        $productIds = $this->productRepository->searchIds(new Criteria($referencedIds), $context->getContext())->getIds();

        if (empty($productIds)) {
            return;
        }

        $criteria = new Criteria($productIds);
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new EqualsAnyFilter(CategoryExtension::RULE_EXTENSION . '.id', $context->getRuleIds()),
            new EqualsFilter(CategoryExtension::RULE_EXTENSION . '.id', null),
        ]));
        $afterProductIds = $this->productRepository->searchIds($criteria, $context->getContext())->getIds();

        if (\count($productIds) === \count($afterProductIds)) {
            return;
        }

        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            $referencedId = $lineItem->getReferencedId();
            if (
                $referencedId === null
                || \in_array($referencedId, $afterProductIds, true)
                || $lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE
            ) {
                continue;
            }

            $errors->add(new InvalidProductError($lineItem->getLabel() ?: $referencedId));
            $lineItem->addExtension(self::INVALID_PRODUCT, new ArrayEntity());
        }
    }
}

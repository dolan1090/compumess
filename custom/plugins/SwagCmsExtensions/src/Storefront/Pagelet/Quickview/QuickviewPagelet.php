<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Storefront\Pagelet\Quickview;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Storefront\Page\Product\Review\ReviewLoaderResult;
use Shopware\Storefront\Pagelet\Pagelet;

class QuickviewPagelet extends Pagelet implements QuickviewPageletInterface
{
    private readonly int $totalReviews;

    public function __construct(
        protected ProductEntity $product,
        private readonly string $listingProductId,
        private readonly ReviewLoaderResult $reviews,
        private readonly PropertyGroupCollection $configuratorSettings
    ) {
        $this->totalReviews = $reviews->getTotalReviews();
    }

    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    public function getListingProductId(): string
    {
        return $this->listingProductId;
    }

    public function getReviews(): ReviewLoaderResult
    {
        return $this->reviews;
    }

    public function getTotalReviews(): int
    {
        return $this->totalReviews;
    }

    public function getConfiguratorSettings(): PropertyGroupCollection
    {
        return $this->configuratorSettings;
    }
}

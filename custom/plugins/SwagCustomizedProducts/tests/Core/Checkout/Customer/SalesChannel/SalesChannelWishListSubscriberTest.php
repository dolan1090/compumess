<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Core\Checkout\Customer\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\System\SalesChannel\Context\CachedSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Core\Test\TestDefaults;
use Shopware\Storefront\Controller\WishlistController;
use Shopware\Storefront\Page\Wishlist\WishlistPageLoader;
use Shopware\Storefront\Page\Wishlist\WishListPageProductCriteriaEvent;
use Shopware\Storefront\Pagelet\Wishlist\GuestWishlistPageletLoader;
use Shopware\Storefront\Pagelet\Wishlist\GuestWishListPageletProductCriteriaEvent;
use Shopware\Tests\Integration\Storefront\Page\StorefrontPageTestBehaviour;
use Swag\CustomizedProducts\Core\Checkout\Customer\SalesChannel\SalesChannelWishListSubscriber;
use Swag\CustomizedProducts\Migration\Migration1565933910TemplateProduct;
use Swag\CustomizedProducts\Test\Helper\ServicesTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelWishListSubscriberTest extends TestCase
{
    use ServicesTrait;
    use StorefrontPageTestBehaviour;

    private CachedSalesChannelContextFactory $salesChannelContextFactory;

    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $systemConfig = $this->getContainer()->get(SystemConfigService::class);
        $systemConfig->set('core.cart.wishlistEnabled', true);

        $this->salesChannelContextFactory = $this->getContainer()->get(SalesChannelContextFactory::class);
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        $this->eventDispatcher = $eventDispatcher;
    }

    public function testGetSubscribedEvents(): void
    {
        $subscribedEvents = SalesChannelWishListSubscriber::getSubscribedEvents();
        $expectedEvents = [
            GuestWishListPageletProductCriteriaEvent::class => 'addCustomizedProductsToWishList',
            WishListPageProductCriteriaEvent::class => 'addCustomizedProductsToWishList',
        ];

        static::assertSame($expectedEvents, $subscribedEvents);
    }

    public function testCriteriaHasCustomizedProductTemplateAssociationForGuestWishListPagelet(): void
    {
        $this->eventDispatcher->addListener(
            GuestWishListPageletProductCriteriaEvent::class,
            static function (GuestWishListPageletProductCriteriaEvent $event): void {
                static::assertTrue(
                    $event->getCriteria()->hasAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)
                );
            },
            -100
        );

        $salesChannelContext = $this->salesChannelContextFactory->create(
            TestDefaults::SALES_CHANNEL,
            TestDefaults::SALES_CHANNEL
        );
        $this->getContainer()->get(GuestWishlistPageletLoader::class)->load(new Request(), $salesChannelContext);
    }

    public function testCriteriaHasCustomizedProductTemplateAssociationForAjaxWishList(): void
    {
        $this->eventDispatcher->addListener(
            WishListPageProductCriteriaEvent::class,
            static function (WishListPageProductCriteriaEvent $event): void {
                static::assertTrue(
                    $event->getCriteria()->hasAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)
                );
            },
            -100
        );

        $customer = $this->createCustomer();
        $salesChannelContext = $this->salesChannelContextFactory->create(
            TestDefaults::SALES_CHANNEL,
            TestDefaults::SALES_CHANNEL,
            [
                SalesChannelContextService::CUSTOMER_ID => $customer->getId(),
            ]
        );
        $this->getContainer()->get(WishlistController::class)->ajaxList(new Request(), $salesChannelContext, $customer);
    }

    public function testCriteriaHasCustomizedProductTemplateAssociationForWishListPage(): void
    {
        $this->eventDispatcher->addListener(
            WishListPageProductCriteriaEvent::class,
            static function (WishListPageProductCriteriaEvent $event): void {
                static::assertTrue(
                    $event->getCriteria()->hasAssociation(Migration1565933910TemplateProduct::PRODUCT_TEMPLATE_INHERITANCE_COLUMN)
                );
            },
            -100
        );

        $salesChannelContext = $this->createSalesChannelContextWithLoggedInCustomerAndWithNavigation();

        $this->getPageLoader()->load(new Request(), $salesChannelContext, $this->createCustomer());
    }

    protected function getPageLoader(): WishlistPageLoader
    {
        return $this->getContainer()->get(WishlistPageLoader::class);
    }
}

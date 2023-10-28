<?php declare(strict_types=1);

namespace Swag\SocialShopping\Test\EventListener;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntitySearchResultLoadedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\Component\Network\GoogleShopping;
use SwagSocialShopping\Component\Network\Instagram;
use SwagSocialShopping\Component\Network\NetworkRegistryInterface;
use SwagSocialShopping\Component\Network\Pinterest;
use SwagSocialShopping\EventListener\SalesChannelTypeHandler;
use SwagSocialShopping\Exception\InvalidNetworkException;
use SwagSocialShopping\SwagSocialShopping;

class SalesChannelTypeHandlerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $expected = [
            'sales_channel_type.search.result.loaded' => 'replaceSocialShoppingTypeWithNetworks',
        ];

        static::assertSame($expected, SalesChannelTypeHandler::getSubscribedEvents());
    }

    public function testDoesEarlyReturnBecauseNoStorefrontSalesChannelExists(): void
    {
        $handler = $this->getSalesChannelTypeHandler([
            new GoogleShopping(),
            new Instagram(),
            new Pinterest(),
            new Facebook(),
        ], 0);

        $event = $this->createMock(EntitySearchResultLoadedEvent::class);
        $result = $this->createMock(EntitySearchResult::class);

        $result->expects(static::never())->method('add');

        $event->method('getResult')->willReturn($result);
        $event->method('getContext')->willReturn(Context::createDefaultContext());

        $handler->replaceSocialShoppingTypeWithNetworks($event);
    }

    public function testThrowsExceptionIfNetworkIsNotInstanceOfNetworkInterface(): void
    {
        $handler = $this->getSalesChannelTypeHandler([
            new ProductEntity(),
        ], 3);

        $event = $this->createMock(EntitySearchResultLoadedEvent::class);
        $result = $this->createMock(EntitySearchResult::class);

        $collection = $this->createMock(EntityCollection::class);
        $collection->expects(static::once())->method('remove')
            ->with(SwagSocialShopping::SALES_CHANNEL_TYPE_SOCIAL_SHOPPING);

        $result->method('getEntities')->willReturn($collection);

        $event->method('getResult')->willReturn($result);
        $event->method('getContext')->willReturn(Context::createDefaultContext());

        static::expectException(InvalidNetworkException::class);
        $handler->replaceSocialShoppingTypeWithNetworks($event);
    }

    public function testReplaceSocialShoppingTypeWithNetworks(): void
    {
        $handler = $this->getSalesChannelTypeHandler([
            new GoogleShopping(),
            new Instagram(),
            new Pinterest(),
            new Facebook(),
        ], 3);

        $event = $this->createMock(EntitySearchResultLoadedEvent::class);
        $result = $this->createMock(EntitySearchResult::class);

        $collection = $this->createMock(EntityCollection::class);
        $collection->expects(static::once())->method('remove')
            ->with(SwagSocialShopping::SALES_CHANNEL_TYPE_SOCIAL_SHOPPING);

        $result->method('getEntities')->willReturn($collection);
        $result->expects(static::exactly(4))->method('add');

        $event->method('getResult')->willReturn($result);
        $event->method('getContext')->willReturn(Context::createDefaultContext());

        $handler->replaceSocialShoppingTypeWithNetworks($event);
    }

    private function getSalesChannelTypeHandler(array $networks, int $total): SalesChannelTypeHandler
    {
        $registry = $this->createMock(NetworkRegistryInterface::class);
        $registry->method('getNetworks')->willReturn($networks);

        $repository = $this->createMock(EntityRepository::class);
        $searchResult = $this->createMock(IdSearchResult::class);

        $searchResult->method('getTotal')->willReturn($total);
        $repository->method('searchIds')->willReturn($searchResult);

        return new SalesChannelTypeHandler($registry, $repository);
    }
}

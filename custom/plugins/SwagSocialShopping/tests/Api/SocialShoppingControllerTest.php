<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\SocialShopping\Test\Api;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Swag\SocialShopping\Test\Helper\ServicesTrait;
use SwagSocialShopping\Api\SocialShoppingController;
use SwagSocialShopping\Component\MessageQueue\SocialShoppingValidation;
use SwagSocialShopping\Component\Network\Facebook;
use SwagSocialShopping\Component\Network\GoogleShopping;
use SwagSocialShopping\Component\Network\Instagram;
use SwagSocialShopping\Component\Network\Pinterest;
use SwagSocialShopping\Exception\SocialShoppingSalesChannelNotFoundException;
use Symfony\Component\Messenger\TraceableMessageBus;

class SocialShoppingControllerTest extends TestCase
{
    use ServicesTrait;

    private SocialShoppingController $socialShoppingController;

    protected function setUp(): void
    {
        /** @var SocialShoppingController $controller */
        $controller = $this->getContainer()->get(SocialShoppingController::class);
        $this->socialShoppingController = $controller;
    }

    public function testGetNetworks(): void
    {
        $responseContent = $this->socialShoppingController->getNetworks()->getContent();
        static::assertIsString($responseContent);
        $networks = \json_decode($responseContent, true);
        static::assertCount(4, $networks);
        static::assertArrayHasKey((new Facebook())->getName(), $networks);
        static::assertSame($networks[(new Facebook())->getName()], Facebook::class);
        static::assertArrayHasKey((new Pinterest())->getName(), $networks);
        static::assertSame($networks[(new Pinterest())->getName()], Pinterest::class);
        static::assertArrayHasKey((new Instagram())->getName(), $networks);
        static::assertSame($networks[(new Instagram())->getName()], Instagram::class);
        static::assertArrayHasKey((new GoogleShopping())->getName(), $networks);
        static::assertSame($networks[(new GoogleShopping())->getName()], GoogleShopping::class);
    }

    public function testValidateWithoutSocialShoppingSalesChannelIdThrowsException(): void
    {
        $this->expectException(MissingRequestParameterException::class);
        $this->socialShoppingController->validate(
            new RequestDataBag(),
            Context::createDefaultContext()
        );
    }

    public function testValidateWithNoneExistingSocialShoppingSalesChannelIdThrowsException(): void
    {
        $this->expectException(SocialShoppingSalesChannelNotFoundException::class);
        $this->socialShoppingController->validate(
            new RequestDataBag([
                'social_shopping_sales_channel_id' => Uuid::randomHex(),
            ]),
            Context::createDefaultContext()
        );
    }

    public function testValidateSetsValidateFlagToEntity(): void
    {
        /** @var TraceableMessageBus $shopwareMessageBus */
        $shopwareMessageBus = $this->getContainer()->get('messenger.bus.shopware');
        $shopwareMessageBus->reset();

        $id = Uuid::randomHex();
        $context = Context::createDefaultContext();

        $this->createSocialShoppingSalesChannel($id, [
            'isValidating' => false,
            'productStreamId' => $this->createProductStream(),
        ]);

        $this->socialShoppingController->validate(
            new RequestDataBag([
                'social_shopping_sales_channel_id' => $id,
            ]),
            $context
        );

        $dispatchedMessages = $shopwareMessageBus->getDispatchedMessages();
        static::assertNotEmpty($dispatchedMessages);

        $message = end($dispatchedMessages);
        static::assertInstanceOf(SocialShoppingValidation::class, $message['message']);
    }
}

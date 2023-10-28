<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\SocialShopping\Test\Component\MessageQueue;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Translation\Translator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Test\TestDefaults;
use Swag\SocialShopping\Test\Helper\ServicesTrait;
use SwagSocialShopping\Component\MessageQueue\SocialShoppingValidation;
use SwagSocialShopping\Component\MessageQueue\SocialShoppingValidationHandler;
use SwagSocialShopping\DataAbstractionLayer\Entity\SocialShoppingSalesChannelEntity;
use SwagSocialShopping\Exception\NoProductStreamAssignedException;
use SwagSocialShopping\Exception\SocialShoppingSalesChannelNotFoundException;

class SocialShoppingValidationHandlerTest extends TestCase
{
    use ServicesTrait;

    private SocialShoppingValidationHandler $validationHandler;

    protected function setUp(): void
    {
        $this->validationHandler = $this->getContainer()
            ->get(SocialShoppingValidationHandler::class);

        /** @var Translator $translator */
        $translator = $this->getContainer()->get(Translator::class);
        $translator->injectSettings(
            TestDefaults::SALES_CHANNEL,
            Defaults::LANGUAGE_SYSTEM,
            'en-GB',
            Context::createDefaultContext()
        );
    }

    public function testGetHandledMessages(): void
    {
        static::assertSame(
            [SocialShoppingValidation::class],
            SocialShoppingValidationHandler::getHandledMessages()
        );
    }

    public function testHandleWithNoneExistingSalesChannelIdThrowsError(): void
    {
        $id = Uuid::randomHex();
        $message = new SocialShoppingValidation(
            $id
        );

        $this->expectException(SocialShoppingSalesChannelNotFoundException::class);
        $this->validationHandler->__invoke($message);
    }

    public function testHandleWithoutProductStreamAssignedThrowsError(): void
    {
        $id = Uuid::randomHex();
        $this->createSocialShoppingSalesChannel($id);
        $message = new SocialShoppingValidation(
            $id
        );

        $this->expectException(NoProductStreamAssignedException::class);
        $this->validationHandler->__invoke($message);
    }

    public function testHandle(): void
    {
        $id = Uuid::randomHex();
        $this->createSocialShoppingSalesChannel(
            $id,
            [
                'isValidating' => true,
                'productStream' => [
                    'name' => 'test-product-stream',
                    'filters' => [
                        [
                            'type' => 'equals',
                            'value' => 'example',
                            'field' => 'product.name',
                        ],
                    ],
                ],
            ]
        );
        $message = new SocialShoppingValidation(
            $id
        );

        $this->validationHandler->__invoke($message);

        /** @var EntityRepository $socialShoppingSalesChannelRepository */
        $socialShoppingSalesChannelRepository = $this->getContainer()->get('swag_social_shopping_sales_channel.repository');

        /** @var SocialShoppingSalesChannelEntity|null $salesChannel */
        $salesChannel = $socialShoppingSalesChannelRepository->search(new Criteria([$id]), Context::createDefaultContext())->get($id);
        static::assertNotNull($salesChannel);
        static::assertFalse($salesChannel->getIsValidating());
    }
}

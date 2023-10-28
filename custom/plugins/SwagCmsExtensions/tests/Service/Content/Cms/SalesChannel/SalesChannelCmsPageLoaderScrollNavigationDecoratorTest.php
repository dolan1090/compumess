<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CmsExtensions\Test\Service\Content\Cms\SalesChannel;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Test\Cart\Common\Generator;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoader;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\CmsExtensions\Service\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderScrollNavigationDecorator;
use Symfony\Component\HttpFoundation\Request;

class SalesChannelCmsPageLoaderScrollNavigationDecoratorTest extends TestCase
{
    use IntegrationTestBehaviour;

    private SalesChannelCmsPageLoaderScrollNavigationDecorator $decorator;

    protected function setUp(): void
    {
        parent::setUp();

        $salesChannelCmsPageLoader = $this->getContainer()->get(SalesChannelCmsPageLoader::class);
        static::assertInstanceOf(SalesChannelCmsPageLoaderInterface::class, $salesChannelCmsPageLoader);

        $this->decorator = new SalesChannelCmsPageLoaderScrollNavigationDecorator(
            $salesChannelCmsPageLoader
        );
    }

    public function testAddScrollNavigationAssociationAddsCorrectAssociation(): void
    {
        $criteria = $this->getMockBuilder(Criteria::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addAssociation'])
            ->getMock();

        $criteria->expects(static::atMost(2))
            ->method('addAssociation');

        $this->decorator->load(
            new Request(),
            $criteria,
            Generator::createSalesChannelContext()
        );
    }
}

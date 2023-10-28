<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\SocialShopping\Test\Installer;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Migration\MigrationCollection;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use SwagSocialShopping\Component\Network\NetworkRegistry;
use SwagSocialShopping\Installer\SalesChannelInstaller;
use SwagSocialShopping\SwagSocialShopping;

class TypeInstallerTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use KernelTestBehaviour;

    public function testDeactivate(): void
    {
        $context = Context::createDefaultContext();

        /** @var NetworkRegistry $networkRegistry */
        $networkRegistry = $this->getContainer()->get(NetworkRegistry::class);
        /** @var array $networks */
        $networks = $networkRegistry->getNetworks();
        $networkCount = \count($networks);

        /** @var EntityRepository $salesChannelTypeRepository */
        $salesChannelTypeRepository = $this->getContainer()->get('sales_channel_type.repository');

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('id'));

        $result = $salesChannelTypeRepository->search($criteria, $context);

        $initialLength = $result->count() - 1;

        $installer = new SalesChannelInstaller($this->getContainer());

        $installer->deactivate(
            new DeactivateContext(
                new SwagSocialShopping(true, ''),
                $context,
                '',
                '',
                $this->createMock(MigrationCollection::class)
            )
        );

        $result = $salesChannelTypeRepository->search($criteria, $context);

        static::assertSame($initialLength - $networkCount, $result->getTotal());
    }

    public function testTranslations(): void
    {
        $context = Context::createDefaultContext();

        /** @var EntityRepository $salesChannelTypeRepository */
        $salesChannelTypeRepository = $this->getContainer()->get('sales_channel_type.repository');

        $criteria = new Criteria();
        $result = $salesChannelTypeRepository->search($criteria, $context);

        static::assertNotEmpty($result->first()->getTranslated());
    }
}

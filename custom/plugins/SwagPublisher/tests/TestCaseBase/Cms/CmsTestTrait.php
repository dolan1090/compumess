<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\TestCaseBase\Cms;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;

trait CmsTestTrait
{
    use CmsBlockTrait;
    use CmsSectionTrait;
    use CmsSlotTrait;
    use KernelTestBehaviour;

    private function getCmsPageRepository(): EntityRepository
    {
        $cmsPageRepository = $this->getContainer()->get('cms_page.repository');

        self::assertInstanceOf(EntityRepository::class, $cmsPageRepository);

        return $cmsPageRepository;
    }

    private function getCmsSectionRepository(): EntityRepository
    {
        $cmsSectionRepository = $this->getContainer()->get('cms_section.repository');

        self::assertInstanceOf(EntityRepository::class, $cmsSectionRepository);

        return $cmsSectionRepository;
    }
}

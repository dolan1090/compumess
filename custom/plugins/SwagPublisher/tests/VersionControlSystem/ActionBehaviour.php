<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest\VersionControlSystem;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait ActionBehaviour
{
    abstract protected static function getContainer(): ContainerInterface;

    private function assertSingleLogExistsWith(string $trueValue, Context $context): void
    {
        $result = $this->getContainer()->get('cms_page_activity.repository')
            ->search(new Criteria(), $context);

        self::assertSame(1, $result->count());
        self::assertTrue($result->first()[$trueValue]);
    }
}

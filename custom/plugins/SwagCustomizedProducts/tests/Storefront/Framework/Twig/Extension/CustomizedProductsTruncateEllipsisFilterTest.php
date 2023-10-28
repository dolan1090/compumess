<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Swag\CustomizedProducts\Test\Storefront\Framework\Twig\Extension;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Swag\CustomizedProducts\Storefront\Framework\Twig\Extension\CustomizedProductsTruncateEllipsisFilter;

class CustomizedProductsTruncateEllipsisFilterTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var CustomizedProductsTruncateEllipsisFilter
     */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = $this->getContainer()->get(CustomizedProductsTruncateEllipsisFilter::class);
    }

    public function testShouldTruncateOneline(): void
    {
        $this->setFilter();

        $testString = 'abcdefghijklmnopqrstuvw';
        static::assertTrue($this->filter->shouldTruncate($testString, 15));
    }

    public function testShouldTruncasteNewlines(): void
    {
        $this->setFilter();

        $testString = 'a
        b
        c
        d
        e';
        static::assertTrue($this->filter->shouldTruncate($testString, 15, 4));
    }

    public function testTruncateOneline(): void
    {
        $this->setFilter();

        $testString = 'abcdefghijklmnopqrstuvw';
        $truncated = 'abcdefghij...';

        static::assertSame($truncated, $this->filter->truncateEllipsis($testString, 10, '...'));
    }

    public function testTruncateMultiplelines(): void
    {
        $this->setFilter();

        $testString = 'testtesttesttesttest
        test
        test123';
        $truncated = 'testtestte...';

        static::assertSame($truncated, $this->filter->truncateEllipsis($testString, 10, '...'));
    }

    private function setFilter(): void
    {
        $filter = $this->getContainer()->get(CustomizedProductsTruncateEllipsisFilter::class);
    }
}

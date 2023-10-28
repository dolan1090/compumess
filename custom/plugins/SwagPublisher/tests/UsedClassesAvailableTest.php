<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPublisherTest;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Symfony\Component\Finder\Finder;

class UsedClassesAvailableTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testClassesAreInstantiable(): void
    {
        $namespace = \str_replace('Tests', '', __NAMESPACE__);

        foreach ($this->getPluginClasses() as $class) {
            $classRelativePath = \str_replace(['.php', '/'], ['', '\\'], $class->getRelativePathname());

            $this->getMockBuilder($namespace . '\\' . $classRelativePath)
                ->disableOriginalConstructor()
                ->getMock();
        }

        // Nothing broke so far, classes seem to be instantiable
        static::assertTrue(true);
    }

    private function getPluginClasses(): Finder
    {
        $finder = new Finder();
        $finder->in(__DIR__ . '/../src');
        $finder->exclude('Test');

        return $finder->files()->name('*.php');
    }
}

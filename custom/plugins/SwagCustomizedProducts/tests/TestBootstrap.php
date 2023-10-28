<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Composer\Autoload\ClassLoader;
use Shopware\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use Shopware\Core\Kernel;

function getProjectDir(): string
{
    if (isset($_SERVER['PROJECT_ROOT']) && is_string($_SERVER['PROJECT_ROOT']) && file_exists($_SERVER['PROJECT_ROOT'])) {
        return $_SERVER['PROJECT_ROOT'];
    }

    $rootDir = dirname(__DIR__, 2);
    $dir = $rootDir;
    while (!file_exists($dir . '/.env')) {
        if ($dir === dirname($dir)) {
            return $rootDir;
        }
        $dir = dirname($dir);
    }

    chdir($dir);

    return $dir;
}

$testProjectDir = getProjectDir();

/** @var ClassLoader $loader */
$loader = require $testProjectDir . '/vendor/autoload.php';
$loader->addPsr4('Swag\\CustomizedProducts\\Test\\', __DIR__);

KernelLifecycleManager::prepare($loader);

$vendorKernelClass = file_exists($testProjectDir . '/src/Kernel.php') ? 'Shopware\Production\Kernel' : Kernel::class;

$bootstrapLocations = [
    [Kernel::class, $testProjectDir . '/src/Core/TestBootstrapper.php'],
    [$vendorKernelClass, $testProjectDir . '/vendor/shopware/core/TestBootstrapper.php'],
    [$vendorKernelClass, $testProjectDir . '/vendor/shopware/platform/src/Core/TestBootstrapper.php'],
];

foreach ($bootstrapLocations as [$class, $file]) {
    if (file_exists($file)) {
        $_SERVER['KERNEL_CLASS'] = $class;
        require_once $file;

        break;
    }
}

$classLoader = (new \Shopware\Core\TestBootstrapper())
    ->setProjectDir($_SERVER['PROJECT_ROOT'] ?? dirname(__DIR__, 4))
    ->setLoadEnvFile(true)
    ->setForceInstallPlugins(true)
    ->addCallingPlugin()
    ->setDatabaseUrl($_SERVER['DATABASE_URL'])
    ->bootstrap()
    ->getClassLoader();

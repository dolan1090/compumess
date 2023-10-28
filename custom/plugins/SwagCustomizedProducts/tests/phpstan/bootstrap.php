<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Core\DevOps\StaticAnalyze\StaticAnalyzeKernel;
use Shopware\Core\Framework\Plugin\KernelPluginLoader\StaticKernelPluginLoader;
use Swag\CustomizedProducts\SwagCustomizedProducts;
use Symfony\Component\Dotenv\Dotenv;

if (!defined('TEST_PROJECT_DIR')) {
    define('TEST_PROJECT_DIR', (function (): string {
        if (isset($_SERVER['PROJECT_ROOT']) && is_string($_SERVER['PROJECT_ROOT']) && file_exists($_SERVER['PROJECT_ROOT'])) {
            return $_SERVER['PROJECT_ROOT'];
        }

        if (file_exists('vendor') && (file_exists('.env') || file_exists('.env.dist'))) {
            return (string) getcwd();
        }

        $dir = $rootDir = __DIR__;
        while (!file_exists($dir . '/vendor')) {
            if ($dir === dirname($dir)) {
                return $rootDir;
            }
            $dir = dirname($dir);
        }

        return $dir;
    })());
}

$_ENV['PROJECT_ROOT'] = $_SERVER['PROJECT_ROOT'] = TEST_PROJECT_DIR;
$classLoader = require TEST_PROJECT_DIR . '/vendor/autoload.php';

if (class_exists(Dotenv::class) && (file_exists(TEST_PROJECT_DIR . '/.env.local.php') || file_exists(TEST_PROJECT_DIR . '/.env') || file_exists(TEST_PROJECT_DIR . '/.env.dist'))) {
    (new Dotenv())->usePutenv()->bootEnv(TEST_PROJECT_DIR . '/.env');
}

/** @var array{'autoload': array{}} $composer */
$composer = json_decode((string) file_get_contents(__DIR__ . '/../../composer.json'), true, 512, \JSON_THROW_ON_ERROR);

$plugins[] = [
    'name' => 'SwagCustomizedProducts',
    'active' => true,
    'version' => '1.0.0',
    'baseClass' => SwagCustomizedProducts::class,
    'managedByComposer' => 0,
    'autoload' => $composer['autoload'],
    'path' => dirname(__DIR__, 2),
];

if (is_dir(TEST_PROJECT_DIR . '/custom/plugins/SwagPayPal')) {
    $plugins[] = [
        'name' => 'SwagPayPal',
        'active' => true,
        'version' => '1.0.0',
        'baseClass' => \Swag\PayPal\SwagPayPal::class,
        'managedByComposer' => 0,
        'autoload' => $composer['autoload'],
        'path' => dirname(__DIR__, 2),
    ];
}

if (is_dir(TEST_PROJECT_DIR . '/custom/plugins/SwagMigrationAssistant')) {
    $plugins[] = [
        'name' => 'SwagMigrationAssistant',
        'active' => true,
        'version' => '1.0.0',
        'baseClass' => \SwagMigrationAssistant\SwagMigrationAssistant::class,
        'managedByComposer' => 0,
        'autoload' => $composer['autoload'],
        'path' => dirname(__DIR__, 2),
    ];
}

$pluginLoader = new StaticKernelPluginLoader($classLoader, null, $plugins);

$kernel = new StaticAnalyzeKernel('customized_products_phpstan', true, $pluginLoader, 'phpstan-test-cache-id');
$kernel->boot();

return $classLoader;

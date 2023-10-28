<?php declare(strict_types=1);
/*
 * (c) shopware AG <info@shopware.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../../../../vendor/autoload.php';

/**
 * Please note that we cannot use exit() in this file!
 * Because this would exit the startup of our analyze tools such as Psalm and Phpstan.
 */
$paypalFound = false;
$files = \scandir('../', \SCANDIR_SORT_ASCENDING);
if (\is_array($files)) {
    foreach ($files as $file) {
        if (\is_dir('../' . $file) && \file_exists('../' . $file . '/src/SwagPayPal.php')) {
            $paypalFound = true;
            $pathToPayPal = '../' . $file . '/vendor/autoload.php';
            if (\file_exists($pathToPayPal)) {
                require_once $pathToPayPal;
            } else {
                echo "Please execute 'composer dump-autoload' in your PayPal directory\n";
            }
        }
    }

    if (!$paypalFound) {
        echo "You need the PayPal plugin for static analyze to work.\n";
    }
} else {
    echo 'Could not scandir ../';
}

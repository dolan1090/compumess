<?php declare(strict_types=1);

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

$finder = Finder::create()
    ->in(__DIR__);

$licenseInformation = <<<'EOF'
(c) shopware AG <info@shopware.com>
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

return (new Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules([
        'header_comment' => [
            'header' => $licenseInformation,
            'separate' => 'bottom',
            'location' => 'after_declare_strict',
            'comment_type' => 'comment'
        ],
        'native_function_invocation' => true,
    ])
    ->setFinder($finder);

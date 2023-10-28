<?php declare(strict_types=1);

require_once __DIR__ . '/dev-ops/tools/config/cs.php';

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->append([__FILE__])
    ->append([__DIR__ . '/dev-ops/tools/scripts/sum-coverage.php']);

return ShopwareEnterpriseCsFixerFactory::create($finder);

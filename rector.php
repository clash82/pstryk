<?php declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/public_html/index.php',
        __DIR__.'/rector.php',
        __DIR__.'/.php-cs-fixer.php',
    ])
    ->withPreparedSets(deadCode: true, codeQuality: true)
    ->withPhpSets(php84: true);

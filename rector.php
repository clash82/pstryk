<?php declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/public_html/index.php',
        __DIR__.'/rector.php',
        __DIR__.'.php-cs-fixer.php',
    ]);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_80,
    ]);
};
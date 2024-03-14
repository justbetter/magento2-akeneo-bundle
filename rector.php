<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Php81\Rector\ClassConst\FinalizePublicClassConstantRector;
use Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $magentoRector = require 'vendor/magento/magento-coding-standard/rector.php';
    $magentoRector($rectorConfig);

    $rectorConfig->paths([__DIR__]);

    // register a single rule
    $rectorConfig->rules([
        InlineConstructorDefaultToPropertyRector::class,
        RemoveFinalFromConstRector::class,
        RemoveEmptyClassMethodRector::class,
        CompleteDynamicPropertiesRector::class,
    ]);

    $rectorConfig->skip([
        FinalizePublicClassConstantRector::class,
        ReadOnlyPropertyRector::class,
        __DIR__ . '/vendor',
    ]);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82
    ]);
}; 

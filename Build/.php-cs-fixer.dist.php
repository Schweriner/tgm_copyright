<?php

declare(strict_types=1);

use TYPO3\CodingStandards\CsFixerConfig;

$config = CsFixerConfig::create();
$config->getFinder()
    ->in([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Configuration',
        __DIR__ . '/../Tests',
    ])
    ->append([
        __DIR__ . '/../ext_localconf.php',
    ]);

$config->setCacheFile(__DIR__ . '/../.build/.php-cs-fixer.cache');

return $config;

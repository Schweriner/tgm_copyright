<?php

declare(strict_types=1);

use a9f\Fractor\Configuration\FractorConfiguration;
use a9f\Typo3Fractor\Set\Typo3LevelSetList;

return FractorConfiguration::configure()
    ->withPaths(
        [
            __DIR__ . '/../Configuration',
            __DIR__ . '/../Resources',
            __DIR__ . '/../ext_localconf.php',
            __DIR__ . '/../ext_tables.sql',
        ],
    )
    ->withSets(
        [
            // Lowest supported version for dual v13/v14 compatibility.
            Typo3LevelSetList::UP_TO_TYPO3_13,
        ],
    );

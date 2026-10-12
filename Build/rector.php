<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/../Classes',
        __DIR__ . '/../Configuration',
        __DIR__ . '/../Tests',
        __DIR__ . '/../ext_localconf.php',
    ]);

    $rectorConfig->importNames();
    // The TYPO3 coding standards keep global-namespace classes (e.g. \Throwable)
    // fully qualified (global_namespace_import.import_classes = false), so rector
    // must not import them either, otherwise both tools would undo each other.
    $rectorConfig->importShortClasses(false);
    $rectorConfig->removeUnusedImports();
    $rectorConfig->phpVersion(80200);

    // The extension supports TYPO3 v13 and v14 in parallel, so the rector
    // target is the LOWEST supported version. Migrating to v14-only APIs
    // (e.g. the Core namespaces for upgrade wizards) would break v13.
    $rectorConfig->sets([
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::TYPE_DECLARATION,
        LevelSetList::UP_TO_PHP_82,
        Typo3LevelSetList::UP_TO_TYPO3_13,
    ]);

    $rectorConfig->skip([
        __DIR__ . '/../ext_emconf.php',
        // Keep the event object parameter even though the body ignores it:
        // AsEventListener derives the listened event from the parameter type.
        CatchExceptionNameMatchingTypeRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
    ]);
};

<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void {
    // The copyright field reuses the configuration of the reference title field
    // and shows the file metadata copyright as placeholder.
    $copyrightFieldConfiguration = $GLOBALS['TCA']['sys_file_reference']['columns']['title'];
    $copyrightFieldConfiguration['label'] = 'Copyright';
    $copyrightFieldConfiguration['config']['placeholder'] = '__row|uid_local|metadata|copyright';

    ExtensionManagementUtility::addTCAcolumns('sys_file_reference', [
        'copyright' => $copyrightFieldConfiguration,
    ]);
    ExtensionManagementUtility::addFieldsToPalette('sys_file_reference', 'imageoverlayPalette', 'copyright', 'after:alternative');
})();

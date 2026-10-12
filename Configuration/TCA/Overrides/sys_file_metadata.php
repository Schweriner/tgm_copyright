<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void {
    $copyrightColumn = [
        'copyright' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:tgm_copyright/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.copyright',
            'description' => 'LLL:EXT:tgm_copyright/Resources/Private/Language/locallang_db.xlf:sys_file_metadata.copyright.description',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $copyrightColumn);
    ExtensionManagementUtility::addToAllTCAtypes('sys_file_metadata', 'copyright', '', 'after:title');
})();

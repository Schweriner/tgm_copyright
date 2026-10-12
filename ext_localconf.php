<?php

declare(strict_types=1);

use TGM\TgmCopyright\Controller\CopyrightController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void {
    ExtensionUtility::configurePlugin(
        'TgmCopyright',
        'Main',
        [
            CopyrightController::class => 'list',
        ],
        [],
        'CType'
    );

    ExtensionUtility::configurePlugin(
        'TgmCopyright',
        'Sitemap',
        [
            CopyrightController::class => 'sitemap',
        ],
        [
            CopyrightController::class => 'sitemap',
        ],
        'CType'
    );
})();

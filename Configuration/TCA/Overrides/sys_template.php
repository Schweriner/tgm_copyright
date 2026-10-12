<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void {
    ExtensionManagementUtility::addStaticFile('tgm_copyright', 'Configuration/TypoScript', 'TgM Picture Copyrights');
})();

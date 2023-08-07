<?php

defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'TgmCopyright',
        'Main',
        'Picture Copyright List'
    );
})();

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tgmcopyright_main'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('tgmcopyright_main', 'FILE:EXT:tgm_copyright/Configuration/Flexform/flexform_main.xml');

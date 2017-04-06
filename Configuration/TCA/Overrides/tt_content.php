<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'TGM.tgm_copyright',
	'Main',
	'Picture Copyright List'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['tgmcopyright_main'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('tgmcopyright_main', 'FILE:EXT:tgm_copyright/Configuration/Flexform/flexform_main.xml');
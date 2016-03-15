<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TGM.' . $_EXTKEY,
	'Main',
	array(
		'Copyright' => 'list,sitemap',
		
	),
	// non-cacheable actions
	array(
		'Copyright' => 'sitemap',
		
	)
);

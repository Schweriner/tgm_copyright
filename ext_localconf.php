<?php

if (!defined('TYPO3')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TgmCopyright',
	'Main',
	[
		\TGM\TgmCopyright\Controller\CopyrightController::class => 'list',
    ],
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TgmCopyright',
	'Sitemap',
	[
		\TGM\TgmCopyright\Controller\CopyrightController::class => 'sitemap',
    ],
	// non-cacheable actions
	[
		\TGM\TgmCopyright\Controller\CopyrightController::class => 'sitemap',
    ]
);
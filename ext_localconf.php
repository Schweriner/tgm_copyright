<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'TGM.' . 'tgm_copyright',
	'Main',
	[
		'Copyright' => 'list,sitemap',

    ],
	// non-cacheable actions
	[
		'Copyright' => 'sitemap',
    ]
);

if (TYPO3_MODE === "BE" && true === version_compare(TYPO3_branch, '9.5', '>=') ) {
    if(true === (bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
        ->get('tgm_copyright', 'copyrightRequired')) {
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/TgmCopyright/RequiredFileReferenceFields');
    }
}
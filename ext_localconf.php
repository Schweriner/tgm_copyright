<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'TgmCopyright',
	'Main',
	[
		\TGM\TgmCopyright\Controller\CopyrightController::class => 'list,sitemap',

    ],
	// non-cacheable actions
	[
		\TGM\TgmCopyright\Controller\CopyrightController::class => 'sitemap',
    ]
);

if(TYPO3_MODE === 'BE') {
    if(true === (bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
            ->get('tgm_copyright', 'copyrightRequired')) {
        $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
        $pageRenderer->loadRequireJsModule('TYPO3/CMS/TgmCopyright/RequiredFileReferenceFields');
    }
}
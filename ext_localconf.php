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

// if(TYPO3_MODE === 'BE') {
(function() {
    if(true === (bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
    ->get('tgm_copyright', 'copyrightRequired')) {
        try {
            $pageRenderer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Page\PageRenderer::class);
            $pageRenderer->loadRequireJsModule('TYPO3/CMS/TgmCopyright/RequiredFileReferenceFields');
        } catch (Exception $exc) {
        }
    }
})();
// }

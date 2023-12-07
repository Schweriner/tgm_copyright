<?php
if (!defined('TYPO3')) {
	die('Access denied.');
}

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
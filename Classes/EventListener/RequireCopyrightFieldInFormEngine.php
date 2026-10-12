<?php

declare(strict_types=1);

namespace TGM\TgmCopyright\EventListener;

use TYPO3\CMS\Backend\Controller\Event\BeforeFormEnginePageInitializedEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Loads the JavaScript module that flags the copyright field as required inside
 * the FormEngine, but only when the extension setting "copyrightRequired" is on.
 *
 * Replaces the former ext_tables.php logic. Loading ext_tables.php is deprecated
 * as of TYPO3 v14.3, and the event is available on both v13 and v14.
 */
final readonly class RequireCopyrightFieldInFormEngine
{
    public function __construct(
        private ExtensionConfiguration $extensionConfiguration,
        private PageRenderer $pageRenderer,
    ) {}

    #[AsEventListener]
    public function __invoke(BeforeFormEnginePageInitializedEvent $event): void
    {
        try {
            $copyrightRequired = (bool)$this->extensionConfiguration->get('tgm_copyright', 'copyrightRequired');
        } catch (\Throwable) {
            return;
        }

        if ($copyrightRequired === false) {
            return;
        }

        $this->pageRenderer->loadJavaScriptModule('@paulbeck/tgmcopyright/copyrightmandatory');
    }
}

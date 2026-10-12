<?php

namespace TGM\TgmCopyright\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2025 Paul Beck <p.beck@nerdost.net>, Nerdost GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use Psr\Http\Message\ResponseInterface;
use TGM\TgmCopyright\Domain\Model\CopyrightReference;
use TGM\TgmCopyright\Domain\Repository\CopyrightReferenceRepository;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * CopyrightController
 */
class CopyrightController extends ActionController
{
    public function __construct(
        private readonly CopyrightReferenceRepository $copyrightReferenceRepository,
        private readonly PageRepository $pageRepository,
        private readonly TypoScriptService $typoScriptService
    ) {}

    /**
     * action list
     */
    public function listAction(): ResponseInterface
    {
        if (isset($this->settings['onlyCurrentPage']) === false) {
            $this->settings['onlyCurrentPage'] = false;
        }

        /** @var array<CopyrightReference> $copyrightReferences */
        $copyrightReferences = $this->copyrightReferenceRepository->findByRootline($this->settings);

        if (count($copyrightReferences) > 0) {
            $this->processExtensionReferences($copyrightReferences);
        }

        $this->view->assignMultiple([
            'copyrightReferences' => $copyrightReferences,
            'copyrights' => $copyrightReferences,
        ]);

        return $this->htmlResponse();
    }

    public function initializeSitemapAction(): void
    {
        $this->request = $this->request->withFormat('xml');
    }

    /**
     * action sitemap
     */
    public function sitemapAction(): ResponseInterface
    {
        $groupedReferences = [];
        $copyrightReferences = $this->copyrightReferenceRepository->findForSitemap($this->settings['rootlines']);

        if ($copyrightReferences !== []) {

            $this->processExtensionReferences($copyrightReferences);

            /** @var CopyrightReference $copyrightReference */
            foreach ($copyrightReferences as $copyrightReference) {
                foreach ($copyrightReference->getUsagePids() as $usagePid) {

                    $additionalArguments = [];

                    if ($copyrightReference->getAdditionalLinkParams() !== '') {
                        $additionalArguments = GeneralUtility::explodeUrl2Array($copyrightReference->getAdditionalLinkParams());
                    }

                    /** @var NormalizedParams $requestAttributes */
                    $requestAttributes = $GLOBALS['TYPO3_REQUEST']->getAttributes()['normalizedParams'];

                    $imagePath = $this->uriBuilder->reset()->setCreateAbsoluteUri(false)
                        ->setTargetPageUid($usagePid)->setArguments($additionalArguments)->buildFrontendUri();

                    $parsedUrl = parse_url($imagePath);

                    if (isset($parsedUrl['host']) === false) {
                        $imagePath = $requestAttributes->getRequestHost() . $imagePath;
                    }

                    // TODO: If the $imagePath was valid instant, an umlaut domain must be excluded from htmlentities
                    $uri = htmlentities($imagePath, ENT_QUOTES, 'UTF-8', true);

                    $hashedUri = md5($uri);

                    $groupedReferences[$hashedUri]['uri'] = $uri;
                    $groupedReferences[$hashedUri]['images'][] = $copyrightReference;

                }
            }
        }

        $this->view->assign('groupedReferences', $groupedReferences);

        return $this->htmlResponse();
    }

    /**
     * @param array<int, CopyrightReference> $copyrightReferences
     */
    private function processExtensionReferences(array &$copyrightReferences): void
    {
        $allExtensionTablesConfiguration = $this->settings['extensiontables'];

        /** @var ContentObjectRenderer $contentObject */
        $contentObject = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        /** @var CopyrightReference $copyrightReference */
        foreach ($copyrightReferences as $copyrightReference) {

            $additionalLinkParams = '';

            if (isset($allExtensionTablesConfiguration[$copyrightReference->getTablenames()])
                && isset($allExtensionTablesConfiguration[$copyrightReference->getTablenames()]['detailPid'])) {
                $singleExtensionTableConfiguration = $allExtensionTablesConfiguration[$copyrightReference->getTablenames()];
                if (is_array($singleExtensionTableConfiguration['detailPid'])) {
                    $tsArray = $this->typoScriptService->convertPlainArrayToTypoScriptArray($singleExtensionTableConfiguration['detailPid']);

                    $rawRecord = $this->pageRepository->getRawRecord($copyrightReference->getTablenames(), $copyrightReference->getUidForeign()) ?? [];

                    $contentObject->start($rawRecord, $copyrightReference->getTablenames());

                    $tsResult = $contentObject->cObjGetSingle($tsArray['_typoScriptNodeValue'], $tsArray);

                    $usagePids = array_map(intval(...), GeneralUtility::trimExplode(',', $tsResult, true));
                } else {
                    $usagePids = [(int)$singleExtensionTableConfiguration['detailPid']];
                }

                if (($singleExtensionTableConfiguration['linkParam'] ?? '') !== '') {
                    $additionalLinkParams = $singleExtensionTableConfiguration['linkParam'] . $copyrightReference->getUidForeign();
                }
            } elseif (in_array($copyrightReference->getTablenames(), ['tt_content', 'pages'], true)) {
                $usagePids = [(int)$copyrightReference->getPid()];
            } else {
                $usagePids = [];
            }

            $copyrightReference->setUsagePids($usagePids);
            $copyrightReference->setAdditionalLinkParams($additionalLinkParams);
        }
    }
}

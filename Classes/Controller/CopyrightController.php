<?php
namespace TGM\TgmCopyright\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Paul Beck <pb@teamgeist-medien.de>, Teamgeist Medien GbR
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
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * CopyrightController
 */
class CopyrightController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * copyrightRepository
     *
     * @var \TGM\TgmCopyright\Domain\Repository\CopyrightRepository
     * @inject
     */
    protected $copyrightRepository = NULL;
    
    /**
     * action list
     * @return void
     */
    public function listAction()
    {
        $fileReferences = $this->copyrightRepository->findByRootline($this->settings['rootlines']);
        $this->view->assign('copyrights', $fileReferences);
    }

    public function initializeSitemapAction()
    {
        $this->request->setFormat('xml');
    }

    /**
     * action sitemap
     * @return void
     */
    public function sitemapAction()
    {
        $groupedReferences = array();
        $fileReferences = $this->copyrightRepository->findForSitemap($this->settings['rootlines']);

        /** @var \TGM\TgmCopyright\Domain\Model\Copyright $fileReference */
        foreach($fileReferences as $fileReference) {
            $groupedReferences[$fileReference->getPid()]['pid'] = $fileReference->getPid();
            $groupedReferences[$fileReference->getPid()]['images'][] = $fileReference;
        }

        $this->view->assign('groupedReferences', $groupedReferences);
    }
}
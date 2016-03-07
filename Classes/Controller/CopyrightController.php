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
        // TODO: Should respect the current Rootline
        $fileReferences = $this->copyrightRepository->findByRootline($this->settings['rootlines']);
        $this->view->assign('copyrights', $fileReferences);
    }
}
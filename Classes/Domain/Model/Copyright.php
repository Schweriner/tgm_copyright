<?php
namespace TGM\TgmCopyright\Domain\Model;


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

/**
 * Copyright
 */
class Copyright extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
{

    /**
     * copyright
     * @var string
     */
    protected $copyright = '';

    /**
     * title of reference
     * @var string
     */
    protected $title = '';
    
    /**
     * Returns the copyright
     *
     * @return string $copyright
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @return bool|string
     */
    public function getTitle()
    {
        if($this->title) {
            return $this->title;
        } else if($this->getOriginalResource()->getProperty('title')) {
            return $this->getOriginalResource()->getProperty('title');
        }
        return FALSE;
    }

}
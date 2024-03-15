<?php
namespace TGM\TgmCopyright\Domain\Model;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Paul Beck <hi@toll-paul.de>, Teamgeist Medien GbR
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
class CopyrightReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference
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
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $tablenames = '';

	/**
	 * @var int
	 */
	protected $uidForeign = 0;

    /**
     * Will be set inside the controller
     * @var array
     */
	protected $usagePids = [];

    /**
     * Will be set inside the controller
     * @var string
     */
	protected $additionalLinkParams = '';


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
        }
        try {
            if($this->getOriginalResource()->getProperty('title')) {
                return $this->getOriginalResource()->getProperty('title');
            }
        } catch(\Exception $e) {
            // May not exists and causes error
        }
        return false;
    }

    /**
     * @return bool|mixed|string
     */
    public function getDescription()
    {
        if($this->description) {
            return $this->description;
        }
        try {
            if($this->getOriginalResource()->getProperty('description')) {
                return $this->getOriginalResource()->getProperty('description');
            }
        } catch(\Exception $e) {
            // May not exists and causes error
        }
        return false;
    }

    /**
     * @return string image public url
     */
    public function getPublicUrl()
    {
        try {
            $originalResource = $this->getOriginalResource();
        } catch (\Exception $e) {
            // May not exists
            return '';
        }
        if($originalResource->getProperty('description')) {
            return $originalResource->getProperty('description');
        }
        if(false === \TYPO3\CMS\Core\Utility\GeneralUtility::isValidUrl($originalResource->getPublicUrl())) {
            /** @var \TYPO3\CMS\Core\Http\NormalizedParams $requestAttributes */
            $requestAttributes = $GLOBALS['TYPO3_REQUEST']->getAttributes()['normalizedParams'];
            return $requestAttributes->getRequestHost() . '/'
                . ltrim($this->getOriginalResource()->getPublicUrl(), '/');
        }
        return $originalResource->getPublicUrl();
    }

    /**
     * @return string
     */
    public function getTablenames()
    {
        return $this->tablenames;
    }

    /**
     * @param string $tablenames
     */
    public function setTablenames($tablenames)
    {
        $this->tablenames = $tablenames;
    }

	/**
	 * @return int
	 */
	public function getUidForeign()
	{
		return $this->uidForeign;
	}

	/**
	 * @param int $uidForeign
	 */
	public function setUidForeign($uidForeign)
	{
		$this->uidForeign = $uidForeign;
	}

    /**
     * @return array
     */
    public function getUsagePids()
    {
        return $this->usagePids;
    }

    /**
     * @param array $usagePids
     */
    public function setUsagePids($usagePids)
    {
        $this->usagePids = $usagePids;
    }

    /**
     * @return string
     */
    public function getAdditionalLinkParams()
    {
        return $this->additionalLinkParams;
    }

    /**
     * @param string $additionalLinkParams
     */
    public function setAdditionalLinkParams($additionalLinkParams)
    {
        $this->additionalLinkParams = $additionalLinkParams;
    }

}

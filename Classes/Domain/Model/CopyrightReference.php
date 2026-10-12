<?php

namespace TGM\TgmCopyright\Domain\Model;

use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

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
/**
 * Copyright
 */
class CopyrightReference extends FileReference
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
     * @var list<int>
     */
    protected $usagePids = [];

    /**
     * Will be set inside the controller
     * @var string
     */
    protected $additionalLinkParams = '';

    /**
     * Returns the copyright. May be null when the reference is hydrated from a
     * sys_file_reference row whose nullable copyright column is not set.
     */
    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function getTitle(): bool|string
    {
        if ($this->title !== '') {
            return $this->title;
        }

        try {
            $title = $this->getOriginalResource()->getProperty('title');
            if ($title !== null && $title !== '') {
                return (string)$title;
            }
        } catch (\Exception) {
            // May not exist and causes error
        }

        return false;
    }

    public function getDescription(): bool|string
    {
        if ($this->description !== '') {
            return $this->description;
        }

        try {
            $description = $this->getOriginalResource()->getProperty('description');
            if ($description !== null && $description !== '') {
                return (string)$description;
            }
        } catch (\Exception) {
            // May not exist and causes error
        }

        return false;
    }

    public function getPublicUrl(): string
    {
        try {
            $originalResource = $this->getOriginalResource();
        } catch (\Exception) {
            // May not exist
            return '';
        }

        $description = $originalResource->getProperty('description');
        if ($description !== null && $description !== '') {
            return (string)$description;
        }

        $publicUrl = (string)$originalResource->getPublicUrl();
        if (GeneralUtility::isValidUrl($publicUrl) === false) {
            /** @var NormalizedParams $requestAttributes */
            $requestAttributes = $GLOBALS['TYPO3_REQUEST']->getAttributes()['normalizedParams'];
            return $requestAttributes->getRequestHost() . '/' . ltrim($publicUrl, '/');
        }

        return $publicUrl;
    }

    public function getTablenames(): string
    {
        return $this->tablenames;
    }

    public function setTablenames(string $tablenames): void
    {
        $this->tablenames = $tablenames;
    }

    public function getUidForeign(): int
    {
        return $this->uidForeign;
    }

    public function setUidForeign(int $uidForeign): void
    {
        $this->uidForeign = $uidForeign;
    }

    /**
     * @return list<int>
     */
    public function getUsagePids(): array
    {
        return $this->usagePids;
    }

    /**
     * @param list<int> $usagePids
     */
    public function setUsagePids(array $usagePids): void
    {
        $this->usagePids = $usagePids;
    }

    public function getAdditionalLinkParams(): string
    {
        return $this->additionalLinkParams;
    }

    public function setAdditionalLinkParams(string $additionalLinkParams): void
    {
        $this->additionalLinkParams = $additionalLinkParams;
    }
}

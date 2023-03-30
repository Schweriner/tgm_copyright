<?php
namespace TGM\TgmCopyright\Domain\Repository;


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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The repository for Copyrights
 */
class CopyrightReferenceRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
{

    /**
     * @param array $settings
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findByRootline($settings) {

        $pidClause = $this->getStatementDefaults($settings['rootlines'], (bool) $settings['onlyCurrentPage']);
        $additionalClause = '';

        if((int)$settings['displayDuplicateImages'] === 0) {
            $additionalClause .= ' GROUP BY file.uid';
        }

        // First main statement, exclude by all possible exclusion reasons
        $preQuery = $this->createQuery();

        $now = time();

        // TODO: Migrate to QueryBuilder for Cross-DB-Engines
        $statement = '
          SELECT ref.* FROM sys_file_reference AS ref
          LEFT JOIN sys_file AS file ON (file.uid=ref.uid_local)
          LEFT JOIN sys_file_metadata AS meta ON (file.uid=meta.file)
          LEFT JOIN pages AS p ON (ref.pid=p.uid)
          WHERE (ref.copyright IS NOT NULL OR meta.copyright!="")
          AND p.deleted=0 AND p.hidden=0 AND (p.starttime=0 OR p.starttime<=' . $now . ') AND (p.endtime=0 OR p.endtime>='. $now .')
          AND file.missing=0 AND file.uid IS NOT NULL
          AND ref.deleted=0 AND ref.hidden=0 AND ref.t3ver_wsid=0 ' . $pidClause . $additionalClause;

        $preQuery->statement($statement);

        $preResults = $preQuery->execute(TRUE);

        // Now check if the foreign record has a endtime field which is expired
        $finalRecords = $this->filterPreResultsReturnUids($preResults);

        // Final select
        if(false === empty($finalRecords)) {
            $finalQuery = $this->createQuery();
            return $finalQuery->statement('SELECT * FROM sys_file_reference WHERE uid IN(' . implode(',', $finalRecords) . ')')->execute();
        }

        return [];
    }

    /**
     * @param string $rootlines
     * @return array
     */
    public function findForSitemap($rootlines) {

        $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version();

        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $sysLanguage = (int) $context->getPropertyFromAspect('language', 'id');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');

        $constraints = [
            $queryBuilder->expr()->eq('ref.sys_language_uid', $sysLanguage),
            $queryBuilder->expr()->eq('missing', 0),
            $queryBuilder->expr()->isNotNull('file.uid'),
            $queryBuilder->expr()->in('file.type', [2, 5]),
        ];

        if ('' !== $rootlines) {
            $constraints[] = $queryBuilder->expr()->in('ref.pid', $this->extendPidListByChildren($rootlines));
        }

        $preResults = $queryBuilder
            ->selectLiteral('ref.uid', 'ref.tablenames', 'ref.uid_foreign')
            ->from('sys_file_reference', 'ref')
            ->leftJoin(
                'ref',
                'sys_file',
                'file',
                $queryBuilder->expr()->eq('file.uid', 'ref.uid_local')
            )
            ->join(
                'ref',
                'pages',
                'p',
                $queryBuilder->expr()->eq('ref.pid', 'p.uid')
            )
            ->where(
                ...$constraints
            )
            ->execute();

        if(version_compare($typo3Version->getVersion(),'11', '<')) {
            $preResults = $preResults->fetchAll();
        } else {
            $preResults = $preResults->fetchAllAssociative();
        }

        // Now check if the foreign record has a endtime field which is expired
        $finalRecords = $this->filterPreResultsReturnUids($preResults);

        // Final select
        if(false === empty($finalRecords)) {

            $queryBuilder->resetQueryParts();
            $records = $queryBuilder
                ->select('*')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->in('uid', $finalRecords)
                )
                ->execute();

            if(version_compare($typo3Version->getVersion(),'11', '<')) {
                $records = $records->fetchAll();
                $objectManager = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\ObjectManager::class);
                $dataMapper = $objectManager->get(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);
            } else {
                $records = $records->fetchAllAssociative();
                $dataMapper = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper::class);
            }

            return $dataMapper->map(\TGM\TgmCopyright\Domain\Model\CopyrightReference::class, $records);
        }

        return [];
    }

    /**
     * This function will remove results which related table records are not hidden by endtime
     * @param array $preResults raw sql results to filter
     * @return array
     */
    public function filterPreResultsReturnUids($preResults) {

        $finalRecords = [];

        foreach($preResults as $preResult) {
            if((isset($preResult['tablenames']) && isset($preResult['uid_foreign']))
                && (strlen($preResult['tablenames']) > 0 && strlen($preResult['uid_foreign']) > 0))
                {

                /*
                 * Thanks to the QueryBuilder we don't have to check end- and starttime, deleted, hidden manually before because of the default RestrictionContainers
                 * Just check if there is a result or not
                 */
                $queryBuilder = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable($preResult['tablenames']);
                $foreignRecord = $queryBuilder
                    ->select('uid')
                    ->from($preResult['tablenames'])
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($preResult['uid_foreign']))
                    )
                    ->execute();

                $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version();

                if(version_compare($typo3Version->getVersion(),'11', '<')) {
                    $foreignRecord = $foreignRecord->fetch();
                } else {
                    $foreignRecord = $foreignRecord->fetchAssociative();
                }

                if($foreignRecord === false || $foreignRecord === false) {
                    // Exclude if nothing found
                    continue;
                }

                // Add the record to the final select if the foreign record is not expired or does not have a field endtime
                $finalRecords[] = $preResult['uid'];
            }
        }

        return $finalRecords;
    }

    /**
     * @param string $rootlines
     * @param bool $onlyCurrentPage
     * @return string
     * @throws \TYPO3\CMS\Core\Context\Exception\AspectNotFoundException
     */
    public function getStatementDefaults($rootlines, $onlyCurrentPage = false) {
        $rootlines = (string) $rootlines;
        $context = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class);
        $sysLanguage = (int) $context->getPropertyFromAspect('language', 'id');
        $defaultStatement = ' AND ref.sys_language_uid=' . $sysLanguage;

        if($onlyCurrentPage === true) {
            $defaultStatement .= ' AND ref.pid=' . $GLOBALS['TSFE']->id;
        } else if($rootlines!=='') {
            $defaultStatement .= ' AND ref.pid IN('.$this->extendPidListByChildren($rootlines).')';
        } else {
            $defaultStatement .= '';
        }

        return $defaultStatement;
    }

    /**
     * Find all ids from given ids and level by Georg Ringer
     * @param string $pidList comma separated list of ids
     * @param int $recursive recursive levels
     * @return string comma separated list of ids
     */
    private function extendPidListByChildren($pidList = '')
    {
        $recursive = 1000;
        $queryGenerator = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\QueryGenerator::class);
        $recursiveStoragePids = $pidList;
        $storagePids = GeneralUtility::intExplode(',', $pidList);
        foreach ($storagePids as $startPid) {
            $pids = $queryGenerator->getTreeList($startPid, $recursive, 0, 1);
            if (strlen($pids) > 0) {
                $recursiveStoragePids .= ',' . $pids;
            }
        }
        return $recursiveStoragePids;
    }
}

<?php

namespace TGM\TgmCopyright\Domain\Repository;

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
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ServerRequestInterface;
use TGM\TgmCopyright\Domain\Model\CopyrightReference;
use TGM\TgmCopyright\Service\DuplicateReferenceFilter;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * The repository for Copyrights
 *
 * @extends Repository<CopyrightReference>
 */
class CopyrightReferenceRepository extends Repository
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly DataMapper $dataMapper,
        private readonly DuplicateReferenceFilter $duplicateReferenceFilter = new DuplicateReferenceFilter(),
    ) {
        parent::__construct();
    }

    /**
     * @param array<string, mixed> $settings
     * @return list<CopyrightReference>
     */
    public function findByRootline(array $settings): array
    {
        $sysLanguage = (int)$this->context->getPropertyFromAspect('language', 'id');
        $now = time();

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');

        // Build WHERE constraints
        $constraints = [
            $queryBuilder->expr()->eq(
                'ref.sys_language_uid',
                $queryBuilder->createNamedParameter($sysLanguage, Connection::PARAM_INT)
            ),
            $queryBuilder->expr()->eq('ref.deleted', 0),
            $queryBuilder->expr()->eq('ref.hidden', 0),
            $queryBuilder->expr()->eq('ref.t3ver_wsid', 0),
            $queryBuilder->expr()->eq('p.deleted', 0),
            $queryBuilder->expr()->eq('p.hidden', 0),
            $queryBuilder->expr()->eq('file.missing', 0),
            $queryBuilder->expr()->isNotNull('file.uid'),
            $queryBuilder->expr()->or(
                $queryBuilder->expr()->isNotNull('ref.copyright'),
                $queryBuilder->expr()->neq('meta.copyright', $queryBuilder->createNamedParameter(''))
            ),
            $queryBuilder->expr()->or(
                $queryBuilder->expr()->eq('p.starttime', 0),
                $queryBuilder->expr()->lte(
                    'p.starttime',
                    $queryBuilder->createNamedParameter($now, Connection::PARAM_INT)
                )
            ),
            $queryBuilder->expr()->or(
                $queryBuilder->expr()->eq('p.endtime', 0),
                $queryBuilder->expr()->gte(
                    'p.endtime',
                    $queryBuilder->createNamedParameter($now, Connection::PARAM_INT)
                )
            ),
        ];

        // Handle pid filtering
        $rootlines = (string)($settings['rootlines'] ?? '');
        if ((bool)($settings['onlyCurrentPage'] ?? false)) {
            $currentPageId = $this->getRequest()->getAttribute('frontend.page.information')?->getId();
            if ($currentPageId !== null && $currentPageId > 0) {
                $constraints[] = $queryBuilder->expr()->eq(
                    'ref.pid',
                    $queryBuilder->createNamedParameter($currentPageId, Connection::PARAM_INT)
                );
            }
        } elseif ($rootlines !== '') {
            $pidList = $this->extendPidListByChildren($rootlines);
            $constraints[] = $queryBuilder->expr()->in('ref.pid', $queryBuilder->createNamedParameter(
                GeneralUtility::intExplode(',', $pidList),
                Connection::PARAM_INT_ARRAY
            ));
        }

        // Build the query
        $queryBuilder
            ->select('ref.uid', 'ref.uid_local', 'ref.tablenames', 'ref.uid_foreign')
            ->from('sys_file_reference', 'ref')
            ->leftJoin(
                'ref',
                'sys_file',
                'file',
                $queryBuilder->expr()->eq('file.uid', $queryBuilder->quoteIdentifier('ref.uid_local'))
            )
            ->leftJoin(
                'file',
                'sys_file_metadata',
                'meta',
                $queryBuilder->expr()->eq('file.uid', $queryBuilder->quoteIdentifier('meta.file'))
            )
            ->leftJoin(
                'ref',
                'pages',
                'p',
                $queryBuilder->expr()->eq('ref.pid', $queryBuilder->quoteIdentifier('p.uid'))
            )
            ->where(...$constraints)
            ->orderBy('ref.uid');

        $preResults = $queryBuilder->executeQuery()->fetchAllAssociative();

        // Now check if the foreign record has an endtime field which is expired
        $finalRecords = $this->filterPreResultsReturnUids($preResults);

        // Apply the setting that decides whether an image is listed more than once
        $finalRecords = $this->duplicateReferenceFilter->filter(
            $settings['displayDuplicateImages'] ?? null,
            $preResults,
            $finalRecords,
        );

        // Final select
        if ($finalRecords !== []) {
            $finalQueryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
            $records = $finalQueryBuilder
                ->select('*')
                ->from('sys_file_reference')
                ->where(
                    $finalQueryBuilder->expr()->eq('deleted', 0),
                    $finalQueryBuilder->expr()->eq('hidden', 0),
                    $finalQueryBuilder->expr()->in('uid', $finalQueryBuilder->createNamedParameter(
                        $finalRecords,
                        Connection::PARAM_INT_ARRAY
                    ))
                )
                ->executeQuery()
                ->fetchAllAssociative();

            return $this->dataMapper->map(CopyrightReference::class, $records);
        }

        return [];
    }

    /**
     * @return list<CopyrightReference>
     */
    public function findForSitemap(?string $rootlines): array
    {
        $sysLanguage = (int)$this->context->getPropertyFromAspect('language', 'id');
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');

        $constraints = [
            $queryBuilder->expr()->eq('ref.sys_language_uid', $sysLanguage),
            $queryBuilder->expr()->eq('missing', 0),
            $queryBuilder->expr()->isNotNull('file.uid'),
            $queryBuilder->expr()->in('file.type', [2, 5]),
            $queryBuilder->expr()->eq('p.no_index', 0),
            $queryBuilder->expr()->eq('p.no_follow', 0),
            $queryBuilder->expr()->eq('p.hidden', 0),
        ];

        if ($rootlines !== '' && $rootlines !== null) {
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
            ->executeQuery();

        $preResults = $preResults->fetchAllAssociative();

        // Now check if the foreign record has an endtime field which is expired
        $finalRecords = $this->filterPreResultsReturnUids($preResults);

        // Final select
        if ($finalRecords !== []) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
            $records = $queryBuilder
                ->select('*')
                ->from('sys_file_reference')
                ->where(
                    $queryBuilder->expr()->in('uid', $finalRecords)
                )
                ->executeQuery();

            $records = $records->fetchAllAssociative();

            return $this->dataMapper->map(CopyrightReference::class, $records);
        }

        return [];
    }

    /**
     * This function will remove results which related table records are not hidden by endtime
     * @param list<array<string, mixed>> $preResults raw sql results to filter
     * @return list<int>
     */
    public function filterPreResultsReturnUids(array $preResults): array
    {
        $finalRecords = [];
        $dbSchema = $this->connectionPool->getConnectionForTable('tt_content')->getSchemaInformation();

        foreach ($preResults as $preResult) {

            if ((isset($preResult['tablenames']) && isset($preResult['uid_foreign']))
                && ((string)$preResult['tablenames'] !== '' && (string)$preResult['uid_foreign'] !== '')
                && in_array($preResult['tablenames'], $dbSchema->listTableNames(), true)
            ) {

                /*
                 * Thanks to the QueryBuilder we don't have to check end- and starttime, deleted, hidden manually before because of the default RestrictionContainers
                 * Just check if there is a result or not
                 */
                $queryBuilder = $this->connectionPool->getQueryBuilderForTable($preResult['tablenames']);
                $foreignRecord = $queryBuilder
                    ->select('uid')
                    ->from($preResult['tablenames'])
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($preResult['uid_foreign']))
                    )
                    ->executeQuery();

                $foreignRecord = $foreignRecord->fetchAssociative();
                if ($foreignRecord === false) {
                    // Exclude if nothing found
                    continue;
                }

                // Add the record to the final select if the foreign record is not expired or does not have a field endtime
                $finalRecords[] = (int)$preResult['uid'];
            }
        }

        return $finalRecords;
    }

    /**
     * @param string $rootlines
     * @param bool $onlyCurrentPage
     * @return string
     * @throws AspectNotFoundException
     * @depcreated will be removed upcoming versions
     */
    public function getStatementDefaults(string $rootlines, bool $onlyCurrentPage = false): string
    {
        $sysLanguage = (int)$this->context->getPropertyFromAspect('language', 'id');
        $defaultStatement = ' AND ref.sys_language_uid=' . $sysLanguage;

        if ($onlyCurrentPage) {
            $defaultStatement .= ' AND ref.pid=' . $this->getRequest()->getAttribute('frontend.page.information')?->getId();
        } elseif ($rootlines !== '') {
            $defaultStatement .= ' AND ref.pid IN(' . $this->extendPidListByChildren($rootlines) . ')';
        } else {
            $defaultStatement .= '';
        }

        return $defaultStatement;
    }

    /**
     * Find all ids from given ids and level by Georg Ringer
     * @param string $pidList comma separated list of ids
     * @return string comma separated list of ids
     */
    private function extendPidListByChildren(string $pidList = ''): string
    {
        $recursive = 1000;
        $recursiveStoragePids = $pidList;
        $storagePids = GeneralUtility::intExplode(',', $pidList);
        foreach ($storagePids as $startPid) {
            $pids = $this->getTreeList($startPid, $recursive);
            if ($pids !== '') {
                $recursiveStoragePids .= ',' . $pids;
            }
        }

        return $recursiveStoragePids;
    }

    /**
     * Recursively fetch all descendants of a given page. MODIFIED:
     * Copied from TYPO3 11's \TYPO3\CMS\Core\Database\QueryGenerator.
     * @param int $id uid of the page
     * @param int $depth
     * @param int $begin
     * @param string $permClause
     * @return string comma separated list of descendant pages
     * @throws Exception
     */
    protected function getTreeList(int $id, int $depth, int $begin = 0, string $permClause = ''): string
    {
        if ($id < 0) {
            $id = abs($id);
        }

        $theList = $begin === 0 ? (string)$id : '';

        if ($id !== 0 && $depth > 0) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
            $queryBuilder->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($id, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq('sys_language_uid', 0)
                )
                ->orderBy('uid');
            if ($permClause !== '') {
                $queryBuilder->andWhere(QueryHelper::stripLogicalOperatorPrefix($permClause));
            }

            $statement = $queryBuilder->executeQuery();
            while ($row = $statement->fetchAssociative()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }

                if ($depth > 1) {
                    $theSubList = $this->getTreeList((int)$row['uid'], $depth - 1, $begin - 1, $permClause);
                    if ($theList !== '' && $theList !== '0' && $theSubList !== '' && ($theSubList[0] !== ',')) {
                        $theList .= ',';
                    }

                    $theList .= $theSubList;
                }
            }
        }

        return $theList;
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}

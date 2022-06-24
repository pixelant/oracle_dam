<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Abstract repository for local TYPO3 database interaction.
 */
abstract class AbstractLocalRepository
{
    public const TABLE_NAME = '';

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(static::TABLE_NAME);
    }
}

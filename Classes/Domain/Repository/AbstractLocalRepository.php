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
     * @param int $uid
     * @param array $data
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(int $uid, array $data): void
    {
        if (count($data) === 0) {
            return;
        }

        $queryBuilder = $this->getQueryBuilder();

        foreach ($data as $field => $value) {
            $queryBuilder->set($field, $value);
        }

        $queryBuilder->set('tstamp', time());

        $queryBuilder
            ->update(static::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute();
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(static::TABLE_NAME);
    }
}

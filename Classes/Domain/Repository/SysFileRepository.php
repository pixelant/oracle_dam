<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Result;

/**
 * For handling files in the sys_file table.
 */
class SysFileRepository extends AbstractLocalRepository
{
    public const TABLE_NAME = 'sys_file';
    public const FIELD_ASSET_ID = 'tx_oracledam_id';
    public const FIELD_ASSET_VERSION = 'tx_oracledam_version';
    public const FIELD_FILE_TIMESTAMP = 'tx_oracledam_file_timestamp';
    public const FIELD_METADATA_TIMESTAMP = 'tx_oracledam_metadata_timestamp';

    /**
     * Find a file record by UID.
     *
     * @param int $uid
     * @return array|null
     * @throws \UnexpectedValueException
     */
    public function findByUid(int $uid): ?array
    {
        $queryBuilder = $this->getQueryBuilder();

        $result = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute();

        if (!$result instanceof Result) {
            throw new \UnexpectedValueException(
                'Query did not return object of type ' . Result::class,
                1656075346055
            );
        }

        // @phpstan-ignore-next-line
        return $result->fetch(FetchMode::ASSOCIATIVE) ?: null;
    }

    /**
     * @param int $uid
     * @return string|null
     * @throws DBALException
     */
    public function getAssetIdentifier(int $uid): ?string
    {
        return $this->findByUid($uid)[self::FIELD_ASSET_ID] ?? null;
    }

    /**
     * @param int $uid
     * @return string
     * @throws DBALException
     */
    public function getAssetVersion(int $uid): string
    {
        return $this->findByUid($uid)[self::FIELD_ASSET_VERSION] ?? '';
    }

    /**
     * Returns all file records with an asset ID from Oracle.
     *
     * @return array
     * @throws \UnexpectedValueException
     */
    public function findFromOracle(): array
    {
        $queryBuilder = $this->getQueryBuilder();

        $result = $queryBuilder
            ->select('f.*', 'm.uid as metadata_uid')
            ->from(self::TABLE_NAME, 'f')
            ->join(
                'f',
                'sys_file_metadata',
                'm',
                $queryBuilder->expr()->eq('f.uid', $queryBuilder->quoteIdentifier('m.file'))
            )
            ->where(
                $queryBuilder->expr()->isNotNull(self::FIELD_ASSET_ID)
            )
            ->orderBy('name')
            ->execute();

        if (!$result instanceof Result) {
            throw new \UnexpectedValueException(
                'Query did not return object of type ' . Result::class,
                1656144003193
            );
        }

        // @phpstan-ignore-next-line
        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }
}

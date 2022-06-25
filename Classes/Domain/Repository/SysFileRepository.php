<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Result;

/**
 * For handling files in the sys_file table.
 */
class SysFileRepository extends AbstractLocalRepository
{
    public const TABLE_NAME = 'sys_file';
    public const FIELD_ASSET_ID = 'tx_oracledam_id';
    public const FIELD_FILE_TIMESTAMP = 'tx_oracledam_file_timestamp';
    public const FIELD_METADATA_TIMESTAMP = 'tx_oracledam_metadata_timestamp';

    /**
     * @param int $fileUid
     * @return string|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getAssetIdentifier(int $fileUid): ?string
    {
        $queryBuilder = $this->getQueryBuilder();

        $result = $queryBuilder
            ->select('tx_oracledam_id')
            ->from(self::TABLE_NAME)
            ->where($queryBuilder
                ->expr()
                ->eq('uid', $queryBuilder
                    ->createNamedParameter($fileUid, \PDO::PARAM_INT)))
            ->execute();

        if (!$result instanceof Result) {
            throw new \UnexpectedValueException(
                'Query did not return object of type ' . Result::class,
                1656075346055
            );
        }

        return $result->fetch()[self::FIELD_ASSET_ID] ?? null;
    }

    /**
     * @param int $fileUid
     * @param array $data
     * @return void
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(int $fileUid, array $data): void
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
            ->update(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($fileUid, \PDO::PARAM_INT)
                )
            )
            ->execute();
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

        return $result->fetchAll(FetchMode::ASSOCIATIVE);
    }
}

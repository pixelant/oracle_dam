<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Functional\Domain\Repository;

use Doctrine\DBAL\FetchMode;
use Oracle\Typo3Dam\Domain\Repository\SysFileRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class SysFileRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oracle_dam'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/FilesAndMetadata.csv');

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');
        $connection->update(
            'sys_file',
            ['tx_oracledam_id' => null],
            ['uid' => 3]
        );
    }

    /**
     * @test
     */
    public function findByUidReturnsCorrectRecord(): void
    {
        $subject = new SysFileRepository();

        $result = $subject->findByUid(1);

        self::assertIsArray($result, 'findByUid() returns array');

        self::assertEquals(
            1,
            $result['uid'],
            'findByUid() returns correct UID'
        );

        self::assertEquals(
            'CONT0123456789ABCDEF0123456789ABCDEF',
            $result['tx_oracledam_id'],
            'findByUid() returns correct Oracle DAM ID'
        );
    }

    /**
     * @test
     */
    public function findByUidOnNonOracleAssetReturnsNull(): void
    {
        $subject = new SysFileRepository();

        $result = $subject->findByUid(3);

        self::assertNull(
            $result,
            'findByUid() returns null if file is not an Oracle asset'
        );
    }

    /**
     * @test
     */
    public function getAssetIdentifierReturnsCorrectValue()
    {
        $subject = new SysFileRepository();

        $result = $subject->getAssetIdentifier(1);

        self::assertEquals(
            'CONT0123456789ABCDEF0123456789ABCDEF',
            $result,
            'getAssetIdentifier() returns correct Oracle DAM ID'
        );
    }

    /**
     * @test
     */
    public function getAssetIdentifierOnNonOracleAssetReturnsNull()
    {
        $subject = new SysFileRepository();

        $result = $subject->getAssetIdentifier(3);

        self::assertNull(
            $result,
            'getAssetIdentifier() returns null if file is not an Oracle asset'
        );
    }

    /**
     * @test
     */
    public function getAssetVersionReturnsCorrectValue()
    {
        $subject = new SysFileRepository();

        $result = $subject->getAssetVersion(2);

        self::assertEquals(
            '2',
            $result,
            'getAssetVersion() returns correct Oracle DAM ID'
        );
    }

    /**
     * @test
     */
    public function getAssetIdentifierOnNonOracleAssetReturnsEmptyString()
    {
        $subject = new SysFileRepository();

        $result = $subject->getAssetVersion(3);

        self::assertEmpty(
            $result,
            'getAssetVersion() returns empty string if file is not an Oracle asset'
        );
    }

    /**
     * @test
     */
    public function findFromOracleReturnsCorrectResult()
    {
        $subject = new SysFileRepository();

        $result = $subject->findFromOracle();

        self::assertCount(
            2,
            $result,
            'findFromOracle() returns two records'
        );

        self::assertContains(
            'CONT0123456789ABCDEF0123456789ABCDEF',
            array_column($result, SysFileRepository::FIELD_ASSET_ID),
            'findFromOracle() result includes record 1\'s ID'
        );

        self::assertContains(
            'CONTABCDEF01234567890ABCDEF123456789',
            array_column($result, SysFileRepository::FIELD_ASSET_ID),
            'findFromOracle() result includes record 2\'s ID'
        );
    }

    /**
     * @test
     */
    public function updateChangesRecord()
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');

        // @phpstan-ignore-next-line
        $recordBeforeUpdate = $connection
            ->select(['*'], SysFileRepository::TABLE_NAME, ['uid' => 1])
            ->fetch(FetchMode::ASSOCIATIVE);

        $subject = new SysFileRepository();

        $newName = 'newName';

        $subject->update(1, [
            'name' => $newName,
        ]);

        // @phpstan-ignore-next-line
        $recordAfterUpdate = $connection
            ->select(['*'], SysFileRepository::TABLE_NAME, ['uid' => 1])
            ->fetch(FetchMode::ASSOCIATIVE);

        self::assertIsArray($recordBeforeUpdate, '$recordBeforeUpdate is array');

        self::assertIsArray($recordAfterUpdate, '$recordAfterUpdate is array');

        self::assertNotEquals(
            $recordBeforeUpdate,
            $recordAfterUpdate,
            '$recordBeforeUpdate and $recordAfterUpdate are not equal'
        );

        self::assertNotEquals(
            $recordBeforeUpdate['tstamp'],
            $recordAfterUpdate['tstamp'],
            'Timestamp has changed'
        );

        self::assertEquals(
            $newName,
            $recordAfterUpdate['name'],
            'Name has changed'
        );
    }
}

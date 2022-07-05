<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Functional\Domain\Repository;

use Doctrine\DBAL\FetchMode;
use Oracle\Typo3Dam\Domain\Repository\SysFileMetadataRepository;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class SysFileMetadataRepositoryTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oracle_dam'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/FilesAndMetadata.csv');
    }

    /**
     * @test
     */
    public function updateChangesRecord()
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('sys_file');

        // @phpstan-ignore-next-line
        $recordBeforeUpdate = $connection
            ->select(['*'], SysFileMetadataRepository::TABLE_NAME, ['uid' => 1])
            ->fetch(FetchMode::ASSOCIATIVE);

        $subject = new SysFileMetadataRepository();

        $newDescription = 'newDescription';

        $subject->update(1, [
            'description' => $newDescription,
        ]);

        // @phpstan-ignore-next-line
        $recordAfterUpdate = $connection
            ->select(['*'], SysFileMetadataRepository::TABLE_NAME, ['uid' => 1])
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
            $newDescription,
            $recordAfterUpdate['description'],
            'Description has changed'
        );
    }
}

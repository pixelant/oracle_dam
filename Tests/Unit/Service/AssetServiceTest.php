<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Service;

use Oracle\Typo3Dam\Service\AssetService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AssetServiceTest extends UnitTestCase
{
    /**
     * @test
     */
    public function createLocalAssetCopyReturnsExistingFile(): void
    {
        $fileMock = self::createMock(File::class);

        $subjectMock = self::createMock(AssetService::class);

        $subjectMock
            ->method('createLocalAssetCopy')
            ->willReturn($fileMock);

        $resultFile = $subjectMock->createLocalAssetCopy('1');

        self::assertEquals(
            $fileMock,
            $resultFile,
            'Existing file is returned'
        );
    }
}

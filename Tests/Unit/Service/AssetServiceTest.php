<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Service;

use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Domain\Repository\AssetRepository;
use Oracle\Typo3Dam\Domain\Repository\SysFileMetadataRepository;
use Oracle\Typo3Dam\Domain\Repository\SysFileRepository;
use Oracle\Typo3Dam\Service\AssetService;
use Oracle\Typo3Dam\Service\Exception\AssetDoesNotExistException;
use Oracle\Typo3Dam\Service\Exception\FileIsNotAnAssetException;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;
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

    /**
     * @test
     */
    public function synchronizeMetadataWithNonOracleFileThrowsException(): void
    {
        $fileMock = self::createMock(File::class);

        $fileMock
            ->method('getUid')
            ->willReturn(0);

        $fileRepositoryMock = self::createMock(SysFileRepository::class);

        $fileRepositoryMock
            ->method('getAssetIdentifier')
            ->willReturn(null);

        $subject = new AssetService(
            self::createMock(ExtensionConfigurationManager::class),
            self::createMock(AssetRepository::class),
            $fileRepositoryMock,
            self::createMock(SysFileMetadataRepository::class),
            self::createMock(ResourceFactory::class),
            self::createMock(EventDispatcher::class)
        );

        $this->expectException(FileIsNotAnAssetException::class);

        $subject->synchronizeMetadata($fileMock);
    }

    /**
     * @test
     */
    public function synchronizeMetadataWithUnknownIdThrowsException(): void
    {
        $fileMock = self::createMock(File::class);

        $fileMock
            ->method('getUid')
            ->willReturn(0);

        $assetRepositoryMock = self::createMock(AssetRepository::class);

        $assetRepositoryMock
            ->method('findById')
            ->willReturn(null);

        $fileRepositoryMock = self::createMock(SysFileRepository::class);

        $fileRepositoryMock
            ->method('getAssetIdentifier')
            ->willReturn('idString');

        $subject = new AssetService(
            self::createMock(ExtensionConfigurationManager::class),
            $assetRepositoryMock,
            $fileRepositoryMock,
            self::createMock(SysFileMetadataRepository::class),
            self::createMock(ResourceFactory::class),
            self::createMock(EventDispatcher::class)
        );

        $this->expectException(AssetDoesNotExistException::class);

        $subject->synchronizeMetadata($fileMock);
    }

    /**
     * @test
     */
    public function updateLocalAssetWithNonOracleFileThrowsException(): void
    {
        $fileMock = self::createMock(File::class);

        $fileMock
            ->method('getUid')
            ->willReturn(0);

        $fileRepositoryMock = self::createMock(SysFileRepository::class);

        $fileRepositoryMock
            ->method('getAssetIdentifier')
            ->willReturn(null);

        $subject = new AssetService(
            self::createMock(ExtensionConfigurationManager::class),
            self::createMock(AssetRepository::class),
            $fileRepositoryMock,
            self::createMock(SysFileMetadataRepository::class),
            self::createMock(ResourceFactory::class),
            self::createMock(EventDispatcher::class)
        );

        $this->expectException(FileIsNotAnAssetException::class);

        $subject->updateLocalAsset($fileMock);
    }

    /**
     * @test
     */
    public function updateLocalAssetWithUnknownIdThrowsException(): void
    {
        $fileMock = self::createMock(File::class);

        $fileMock
            ->method('getUid')
            ->willReturn(0);

        $assetRepositoryMock = self::createMock(AssetRepository::class);

        $assetRepositoryMock
            ->method('findById')
            ->willReturn(null);

        $fileRepositoryMock = self::createMock(SysFileRepository::class);

        $fileRepositoryMock
            ->method('getAssetIdentifier')
            ->willReturn('idString');

        $subject = new AssetService(
            self::createMock(ExtensionConfigurationManager::class),
            $assetRepositoryMock,
            $fileRepositoryMock,
            self::createMock(SysFileMetadataRepository::class),
            self::createMock(ResourceFactory::class),
            self::createMock(EventDispatcher::class)
        );

        $this->expectException(AssetDoesNotExistException::class);

        $subject->updateLocalAsset($fileMock);
    }
}

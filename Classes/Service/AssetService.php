<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Service;

use GuzzleHttp\Exception\ClientException;
use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Domain\Repository\AssetRepository;
use Oracle\Typo3Dam\Domain\Repository\SysFileMetadataRepository;
use Oracle\Typo3Dam\Domain\Repository\SysFileRepository;
use Oracle\Typo3Dam\Service\Exception\AssetDoesNotExistException;
use Oracle\Typo3Dam\Service\Exception\FileIsNotAnAssetException;
use Oracle\Typo3Dam\Service\Exception\LocalAssetCopyCreationException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\IllegalFileExtensionException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AssetService implements SingletonInterface
{
    /**
     * @var ExtensionConfigurationManager
     */
    protected $configuration;

    /**
     * @var AssetRepository
     */
    protected $assetRepository;

    /**
     * @var SysFileRepository
     */
    protected $fileRepository;

    /**
     * @var SysFileMetadataRepository
     */
    protected $metadataRepository;

    /**
     * @var ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @param ExtensionConfigurationManager $configuration
     * @param AssetRepository $assetRepository
     * @param SysFileRepository $fileRepository
     * @param SysFileMetadataRepository $metadataRepository
     * @param ResourceFactory $resourceFactory
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        ExtensionConfigurationManager $configuration,
        AssetRepository $assetRepository,
        SysFileRepository $fileRepository,
        SysFileMetadataRepository $metadataRepository,
        ResourceFactory $resourceFactory,
        EventDispatcher $eventDispatcher
    ) {
        $this->configuration = $configuration;
        $this->assetRepository = $assetRepository;
        $this->fileRepository = $fileRepository;
        $this->metadataRepository = $metadataRepository;
        $this->resourceFactory = $resourceFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Downloads an asset file if it doesn't already exist. Returns the local File object.
     *
     * @param string $id
     *
     * @return File The local file representation
     * @throws AssetDoesNotExistException
     */
    public function createLocalAssetCopy(string $id): ?File
    {
        $file = $this->findLocalAssetCopy($id);
        if ($file !== null) {
            return $file;
        }

        $assetInfo = $this->assetRepository->findById($id);

        if ($assetInfo === null) {
            throw new AssetDoesNotExistException(
                'The asset with ID ' . $id . ' does not exist or is not accessible in the DAM.',
                1656571699018
            );
        }

        $assetData = $this->assetRepository->downloadByUrl($assetInfo['url']);

        try {
            $downloadFolder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier(
                $this->configuration->getDownloadFolder()
            );
        } catch (FolderDoesNotExistException $exception) {
            $storage = $this->resourceFactory->getStorageObjectFromCombinedIdentifier(
                $this->configuration->getDownloadFolder()
            );

            [, $folderPath] = explode(':', $this->configuration->getDownloadFolder());

            $downloadFolder = $storage->createFolder($folderPath);
        }

        $file = $downloadFolder->createFile($assetInfo['name']);
        $file->setContents($assetData);

        $this->updateFileRecord($file, $assetInfo['version'], true, $id);

        $this->synchronizeMetadata($file);

        return $file;
    }

    /**
     * Returns a File object equivalent of an asset. Or null if it doesn't exist.
     *
     * @param string $id
     *
     * @return File|null
     */
    protected function findLocalAssetCopy(string $id): ?File
    {
        $queryBuilder = $this->getFileQueryBuilder();

        // @phpstan-ignore-next-line
        $fileUid = $queryBuilder
            ->select('uid')
            ->from('sys_file')
            ->where($queryBuilder->expr()->eq(
                'tx_oracledam_id',
                $queryBuilder->createNamedParameter($id)
            ))
            ->execute()
            ->fetchColumn();

        if ($fileUid === false) {
            return null;
        }

        return $this->resourceFactory->getFileObject($fileUid);
    }

    /**
     * Returns a QueryBuilder object for the sys_file table.
     *
     * @return QueryBuilder
     */
    protected function getFileQueryBuilder(): QueryBuilder
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
    }

    /**
     * Update a sys_file record with DAM asset timestamp and relation information.
     *
     * @param File $file The local file UID
     * @param string|null $newFileVersion If not null, contains the version string of the new file version.
     * @param bool $changedMetadata True if metadata has been changed
     * @param string|null $assetId The Oracle asset ID. Not needed unless it's the first time record is written.
     */
    protected function updateFileRecord(
        File $file,
        ?string $newFileVersion,
        bool $changedMetadata,
        ?string $assetId = null
    ): void {
        $data = [];

        if ($newFileVersion !== null) {
            $data[SysFileRepository::FIELD_ASSET_VERSION] = $newFileVersion;
            $data[SysFileRepository::FIELD_FILE_TIMESTAMP] = time();
        }

        if ($changedMetadata) {
            $data[SysFileRepository::FIELD_METADATA_TIMESTAMP] = time();
        }

        if (null !== $assetId) {
            $data[SysFileRepository::FIELD_ASSET_ID] = $assetId;
        }

        $this->fileRepository->update($file->getUid(), $data);
    }

    /**
     * Synchronize metadata for a particular file UID.
     *
     * @param File $file The FAL file UID
     * @throws FileIsNotAnAssetException
     * @throws AssetDoesNotExistException
     */
    public function synchronizeMetadata(File $file): void
    {
        $id = $this->getAssetIdentifierForFile($file);

        if ($id === null) {
            throw new FileIsNotAnAssetException(
                'The file is not an Oracle DAM asset: ' . $file->getIdentifier() . ' [' . $file->getUid() . ']',
                1656235334707
            );
        }

        $assetInfo = $this->assetRepository->findById($id);

        if ($assetInfo === null) {
            throw new AssetDoesNotExistException(
                'The file does not exist in Oracle DAM. Asset ID ' . $id . ', represented locally by '
                . $file->getIdentifier() . ' [' . $file->getUid() . ']',
                1656235537826
            );
        }

        $this->metadataRepository->update(
            (int)$file->getMetaData()->offsetGet('uid'),
            [
                'title' => $assetInfo['title'],
                'caption' => $assetInfo['caption'],
                'alternative' => $assetInfo['alternate_text'],
            ]
        );

        $this->updateFileRecord($file, null, true);
    }

    /**
     * If it has changed remotely, update local file content for a particular file UID.
     *
     * @param File $file The FAL file UID
     * @throws FileIsNotAnAssetException
     * @throws AssetDoesNotExistException
     * @throws LocalAssetCopyCreationException
     */
    public function updateLocalAsset(File $file): void
    {
        $assetId = $this->getAssetIdentifierForFile($file);

        if ($assetId === null) {
            throw new FileIsNotAnAssetException(
                'The file is not an Oracle DAM asset: ' . $file->getIdentifier() . ' [' . $file->getUid() . ']',
                1656250692885
            );
        }

        $assetInfo = $this->assetRepository->findById($assetId);

        if ($assetInfo === null) {
            throw new AssetDoesNotExistException(
                'The file does not exist in Oracle DAM. Asset ID ' . $assetId . ', represented locally by '
                . $file->getIdentifier() . ' [' . $file->getUid() . ']',
                1656250797016
            );
        }

        if ($this->fileRepository->getAssetVersion($file->getUid()) === $assetInfo['version']) {
            return;
        }

        $temporaryFileName = GeneralUtility::tempnam('oracle_dam');

        try {
            $newContent = $this->assetRepository->downloadByUrl($assetInfo['url']);
        } catch (ClientException $exception) {
            throw new LocalAssetCopyCreationException(
                'Error when downloading asset ' . $assetId . ': ' . $exception->getMessage()
                . ' (' . $exception->getCode() . ')',
                1656577056266
            );
        }

        if (file_put_contents($temporaryFileName, $newContent) === false) {
            throw new LocalAssetCopyCreationException(
                'Could not write asset  ' . $assetId . ' to temporary file ' . $temporaryFileName,
                1656577156792
            );
        }

        $file->getStorage()->replaceFile($file, $temporaryFileName);

        GeneralUtility::unlink_tempfile($temporaryFileName);

        $this->updateFileRecord($file, $assetInfo['version'], false);
    }

    /**
     * Returns true if the sys_file uid supplied in $fileId is an Oracle DAM file.
     *
     * @param File $file
     */
    protected function isOracleDamFile(File $file): bool
    {
        return $this->getAssetIdentifierForFile($file) !== null;
    }

    /**
     * Returns the Oracle asset ID for the sys_file UID supplied in $fileId.
     *
     * @param File $file
     * @return string|null The Oracle asset ID. Zero if not found or file is not an Oracle DAM asset.
     * @throws \UnexpectedValueException
     */
    protected function getAssetIdentifierForFile(File $file): ?string
    {
        return $this->fileRepository->getAssetIdentifier($file->getUid());
    }
}

<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Service;

use Oracle\Typo3Dam\Api\Exception\PersistMetaDataChangesException;
use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Domain\Repository\AssetRepository;
use Oracle\Typo3Dam\Domain\Repository\SysFileMetadataRepository;
use Oracle\Typo3Dam\Domain\Repository\SysFileRepository;
use Oracle\Typo3Dam\Service\Exception\AssetDoesNotExistException;
use Oracle\Typo3Dam\Service\Exception\FileIsNotAnAssetException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
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
     */
    public function createLocalAssetCopy(string $id): ?File
    {
        $file = $this->findLocalAssetCopy($id);
        if ($file !== null) {
            return $file;
        }

        $assetInfo = $this->assetRepository->findById($id) ?? null;
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

        $this->updateFileRecord($file, true, true, $id);

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
     * @param bool $changedFile True if file has been changed
     * @param bool $changedMetadata True if metadata has been changed
     * @param string|null $assetId The Oracle asset ID. Not needed unless it's the first time record is written.
     */
    protected function updateFileRecord(
        File $file,
        bool $changedFile,
        bool $changedMetadata,
        ?string $assetId = null
    ): void {
        $data = [];

        if ($changedFile) {
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

        $assetInfo = $this->assetRepository->findById($id) ?? null;

        if ($assetInfo === null) {
            throw new AssetDoesNotExistException(
                'The file does not exist in Oracle DAM. Asset ID ' . $id . ', represented locally by '
                . $file->getIdentifier() . ' [' . $file->getUid() . ']',
                1656235537826
            );
        }

        $this->metadataRepository->update(
            $file->getMetaData()->offsetGet('uid'),
            [
                'title' => $assetInfo['title'],
                'caption' => $assetInfo['caption'],
                'alternative' => $assetInfo['alternate_text'],
            ]
        );

        $this->updateFileRecord($file, false, true);
    }

    /**
     * Synchronize file content for a particular file UID.
     *
     * @param int $fileId The FAL file UID
     * @throws FileDoesNotExistException
     */
    public function replaceLocalAsset(int $fileId): void
    {
        $file = $this->resourceFactory->getFileObject($fileId);
        if ($file === null) {
            throw new FileDoesNotExistException(
                'No file found for given UID: ' . $fileId,
                1623070299
            );
        }

        $replacedByAssetIdentifier = $this->getAssetIdentifierForFile($file);

        $replacedByFile = $this->createLocalAssetCopy($replacedByAssetIdentifier);
        if ($replacedByFile === null) {
            throw new FileDoesNotExistException(
                'No file found for given Oracle asset id: ' . $replacedByAssetIdentifier,
                1623306399
            );
        }

        /**
         * Handle file references
         */
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

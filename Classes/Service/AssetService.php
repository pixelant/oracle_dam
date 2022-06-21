<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Service;

use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Domain\Repository\AssetRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
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
     * @param ResourceFactory $resourceFactory
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(
        ExtensionConfigurationManager $configuration,
        AssetRepository $assetRepository,
        ResourceFactory $resourceFactory,
        EventDispatcher $eventDispatcher
    ) {
        $this->configuration   = $configuration;
        $this->assetRepository = $assetRepository;
        $this->resourceFactory = $resourceFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Downloads an asset file if it doesn't already exist. Returns the local File object.
     *
     * @param string $id
     *
     * @return File The local file representation
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     */
    public function createLocalAssetCopy(string $id): ?File
    {
        $fullAssetInfo               = $this->assetRepository->findById($id) ?? null;
        $assetInfo                   = [];
        $assetInfo['id']             = $fullAssetInfo['id'] ?? '';
        $assetInfo['name']           = $fullAssetInfo['name'] ?? '';
        $assetInfo['title']          = $fullAssetInfo['fields']['title'] ?? '';
        $assetInfo['alternate_text'] = $fullAssetInfo['fields']['alternate_text'] ?? '';
        $assetInfo['caption']        = $fullAssetInfo['fields']['caption'] ?? '';
        $assetInfo['modified_date']  = time();
        $rendition                   = array_search(
            'Large',
            array_column($fullAssetInfo['fields']['renditions'], 'name')
        );
        $format                      = array_search(
            'jpg',
            array_column($fullAssetInfo['fields']['renditions'][$rendition]['formats'], 'format')
        );
        $assetInfo['url']
            = $fullAssetInfo['fields']['renditions'][$rendition]['formats'][$format]['links'][0]['href'] ?? '';

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
        return null;
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
     * @param int $fileUid The local file UID
     * @param bool $changedFile True if file has been changed
     * @param bool $changedMetadata True if metadata has been changed
     * @param string|null $assetId
     */
    protected function updateFileRecord(
        int $fileUid,
        bool $changedFile,
        bool $changedMetadata,
        ?string $assetId = null
    ): void {

    }

    /**
     * Synchronize metadata for a particular file UID.
     *
     * @param int $fileId The FAL file UID
     */
    public function synchronizeMetadata(int $fileId): void
    {

    }

    /**
     * Synchronize file content for a particular file UID.
     *
     * @param int $fileId The FAL file UID
     */
    public function replaceLocalMedia(int $fileId): void
    {

    }

    /**
     * Returns true if the sys_file uid suppplied in $fileId is a Oracle DAM file.
     *
     * @param int $fileId
     */
    protected function isOracleDamFile(int $fileId): bool
    {
        return $this->getAssetIdentifierForFile($fileId) !== null;
    }

    /**
     * Returns the Oracle asset ID for the sys_file UID supplied in $fileId.
     *
     * @param int $fileId
     *
     * @return string|null The Oracle asset ID. Zero if not found or file is not an Oracle DAM asset.
     */
    protected function getAssetIdentifierForFile(int $fileId): ?string
    {

    }
}

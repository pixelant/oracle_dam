<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Controller;

use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Controller\AssetListModuleController;
use Oracle\Typo3Dam\Service\AssetService;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AssetListModuleControllerTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $languageServiceMock = self::createMock(LanguageService::class);

        $languageServiceMock
            ->method('getLL')
            ->willReturnArgument(0);

        $GLOBALS['LANG'] = $languageServiceMock;
    }

    /**
     * @test
     */
    public function updateFileActionRetrievesFile(): void
    {
        $fileUid = 1;

        $resourceFactoryMock = self::createMock(ResourceFactory::class);

        $resourceFactoryMock
            ->method('getFileObject')
            ->with($fileUid)
            ->willReturn(self::createMock(File::class));

        GeneralUtility::setSingletonInstance(ResourceFactory::class, $resourceFactoryMock);

        $requestMock = self::createMock(ServerRequest::class);

        $requestMock
            ->method('getQueryParams')
            ->willReturn(['file' => (string)$fileUid]);

        $uriBuilderMock = self::createMock(UriBuilder::class);

        $uriBuilderMock
            ->method('buildUriFromRoute')
            ->with('file_oracleDamAssetList')
            ->willReturnArgument(0);

        $assetServiceMock = self::createMock(AssetService::class);

        GeneralUtility::setSingletonInstance(AssetService::class, $assetServiceMock);

        $subject = new AssetListModuleController(
            self::createMock(ModuleTemplate::class),
            self::createMock(ExtensionConfigurationManager::class),
            $uriBuilderMock
        );

        $response = $subject->updateFileAction($requestMock);

        self::assertEquals(
            302,
            $response->getStatusCode(),
            'updateFileAction() returns redirect response'
        );
    }

    protected function tearDown(): void
    {
        GeneralUtility::resetSingletonInstances([]);

        parent::tearDown();
    }
}

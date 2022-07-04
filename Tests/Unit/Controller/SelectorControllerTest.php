<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Controller;

use Oracle\Typo3Dam\Controller\SelectorController;
use Oracle\Typo3Dam\Service\AssetService;
use Oracle\Typo3Dam\Service\Exception\AssetDoesNotExistException;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class SelectorControllerTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['LANG'] = self::createMock(LanguageService::class);
    }

    /**
     * @test
     */
    public function downloadFileActionCorrectlyForwardsIdsToAssetService(): void
    {
        $ids = ['1', '2', '3'];

        $assetServiceMock = self::createMock(AssetService::class);

        $assetServiceMock
            ->method('createLocalAssetCopy')
            ->withConsecutive(...array_chunk($ids, 1))
            ->willReturnCallback(function ($id) {
                return self::createMock(File::class);
            });

        $requestMock = self::createMock(ServerRequest::class);

        $requestMock
            ->method('getParsedBody')
            ->willReturn(['assets' => implode(',', $ids)]);

        $subject = new SelectorController($assetServiceMock);

        $response = $subject->downloadFileAction($requestMock);

        self::assertEquals(
            200,
            $response->getStatusCode()
        );
    }

    /**
     * @test
     */
    public function downloadFileReturnsErrorOnAssetDoesNotExistException()
    {
        $assetServiceMock = self::createMock(AssetService::class);

        $assetServiceMock
            ->method('createLocalAssetCopy')
            ->willThrowException(new AssetDoesNotExistException());

        $requestMock = self::createMock(ServerRequest::class);

        $requestMock
            ->method('getParsedBody')
            ->willReturn(['assets' => '0']);

        $subject = new SelectorController($assetServiceMock);

        $response = $subject->downloadFileAction($requestMock);

        $responseData = json_decode($response->getBody()->getContents(), true);

        self::assertEmpty($responseData['fileUids'], 'fileUids is empty');

        self::assertCount(1, $responseData['errors'], 'Expect 1 errors');

        self::assertTrue($responseData['success'], 'Success is still returned as true');
    }
}

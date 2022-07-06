<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Domain\Repository;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Oracle\Typo3Dam\Api\Controller\ContentManagementController;
use Oracle\Typo3Dam\Api\OracleApi;
use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use Oracle\Typo3Dam\Domain\Repository\AssetRepository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class AssetRepositoryTest extends UnitTestCase
{
    /**
     * @test
     */
    public function findByIdHandlesNotFoundException()
    {
        $id = 'CONT0123456789ABCDEF0123456789ABCDEF';

        $controllerMock = self::getMockBuilder(ContentManagementController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controllerMock
            ->method('getItem')
            ->with($id)
            ->willThrowException(new ClientException(
                'Pretend 404 Exception',
                new Request('GET', 'test'),
                new Response(404)
            ));

        $apiMock = self::createMock(OracleApi::class);

        $apiMock
            ->method('contentManagement')
            ->willReturn($controllerMock);

        $subject = new AssetRepository(
            self::createMock(ExtensionConfigurationManager::class),
            $apiMock
        );

        $result = $subject->findById($id);

        self::assertNull(
            $result
        );
    }

    /**
     * @test
     */
    public function findByIdReturnsExpectedData()
    {
        $id = 'CONT0123456789ABCDEF0123456789ABCDEF';

        $controllerMock = self::getMockBuilder(ContentManagementController::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controllerMock
            ->method('getItem')
            ->with($id)
            ->willReturn([
                'id' => $id,
                'version' => '1.0',
                'name' => 'theFilename',
                'fields' => [
                    'title' => 'theTitle',
                    'alternate_text' => 'theAlternateText',
                    'caption' => 'theCaption',
                    'renditions' => [
                        [
                            'name' => 'Large',
                            'formats' => [
                                [
                                    'format' => 'jpg',
                                    'links' => [
                                        [
                                            'href' => 'theImageUrl',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

        $apiMock = self::createMock(OracleApi::class);

        $apiMock
            ->method('contentManagement')
            ->willReturn($controllerMock);

        $subject = new AssetRepository(
            self::createMock(ExtensionConfigurationManager::class),
            $apiMock
        );

        $result = $subject->findById($id);

        self::assertEquals(
            [
                'id' => $id,
                'version' => '1.0',
                'name' => 'theFilename',
                'title' => 'theTitle',
                'alternate_text' => 'theAlternateText',
                'caption' => 'theCaption',
                'url' => 'theImageUrl',
            ],
            $result
        );
    }
}

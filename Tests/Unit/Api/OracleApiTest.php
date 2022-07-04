<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Api;

use Oracle\Typo3Dam\Api\Controller\ContentManagementController;
use Oracle\Typo3Dam\Api\OracleApi;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class OracleApiTest extends UnitTestCase
{
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new OracleApi('url', 'tokenUrl', 'scope', 'clientId', 'clientSecret');
    }

    /**
     * @test
     */
    public function contentReturnsCorrectClass(): void
    {
        $content = $this->subject->contentManagement();

        self::assertInstanceOf(
            ContentManagementController::class,
            $content,
            'content() returns instanceof ' . ContentManagementController::class
        );

        self::assertEquals(
            $content,
            $this->subject->contentManagement(),
            'content() returns same instance of ' . ContentManagementController::class
        );
    }
}

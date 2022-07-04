<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Api;

use Oracle\Typo3Dam\Api\Controller\ContentDeliveryController;
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
        $content = $this->subject->content();

        self::assertInstanceOf(
            ContentDeliveryController::class,
            $content,
            'content() returns instanceof ' . ContentDeliveryController::class
        );

        self::assertEquals(
            $content,
            $this->subject->content(),
            'content() returns same instance of ' . ContentDeliveryController::class
        );
    }
}

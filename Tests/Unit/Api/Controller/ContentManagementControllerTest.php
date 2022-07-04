<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Api\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Oracle\Typo3Dam\Api\Controller\ContentManagementController;
use Psr\Http\Message\RequestInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ContentManagementControllerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getItemCallsApiEndpoint(): void
    {
        $id = 'CONT0123456789ABCDEF0123456789ABCDEF';

        $mockHandler = new MockHandler([
            new Response(200, [], '{}'),
        ]);

        $historyContainer = [];

        $history = Middleware::history($historyContainer);

        $handlerStack = HandlerStack::create($mockHandler);
        $handlerStack->push($history);

        $client = new Client(['handler' => $handlerStack]);

        $subject = new ContentManagementController($client);

        $result = $subject->getItem($id);

        /** @var RequestInterface $historicRequest */
        $historicRequest = $historyContainer[0]['request'];

        self::assertIsArray(
            $result,
            'getItem() returns array'
        );

        self::assertEquals(
            '/content/management/api/v1.1/items/' . $id,
            $historicRequest->getUri()->getPath(),
            'getItem() calls correct API endpoint'
        );

        self::assertEquals(
            'GET',
            $historicRequest->getMethod(),
            'getItem() uses GET method'
        );
    }
}

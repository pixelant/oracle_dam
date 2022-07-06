<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Api\Controller;

use GuzzleHttp\Client;

abstract class AbstractController
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}

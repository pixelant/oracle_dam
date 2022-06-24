<?php

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

    /**
     * @param string $id
     *
     * @return array
     */
    protected function getContentItem(string $id): array
    {
        $response = $this->client->request('GET', '/content/management/api/v1.1/items/' . $id);

        return json_decode($response->getBody()->getContents(), true);
    }
}

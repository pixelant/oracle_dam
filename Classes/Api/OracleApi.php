<?php

namespace Oracle\Typo3Dam\Api;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Oracle\Typo3Dam\Api\Controller\ContentDeliveryController;

class OracleApi
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $tokenUrl;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var ContentDeliveryController
     */
    protected $contentDeliveryController;

    /**
     * @var CachePolicy
     */
    protected $cachePolicy;

    /**
     * @param string $url
     * @param string $tokenUrl
     * @param string $scope
     * @param string $clientId
     * @param string $clientSecret
     */
    public function __construct(string $url, string $tokenUrl, string $scope, string $clientId, string $clientSecret)
    {
        $this->url = $url;
        $this->tokenUrl = $tokenUrl;
        $this->scope = $scope;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * Gets the Guzzle client instance used for making calls.
     *
     * @return Client
     */
    protected function getClient()
    {
        if (!($this->client instanceof Client)) {
            $reauth_client  = new Client([
                'base_uri' => $this->tokenUrl . '/oauth2/v1/token',
            ]);
            $reauth_config = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => $this->scope,
            ];
            $grant_type = new ClientCredentials($reauth_client, $reauth_config);
            $oauth = new OAuth2Middleware($grant_type);
            $stack = HandlerStack::create();
            $stack->push($oauth);
            $this->client = new Client([
                'base_uri' => $this->url,
                'handler' => $stack,
                'auth' => 'oauth',
            ]);
        }

        return $this->client;
    }

    /**
     * @return ContentDeliveryController
     */
    public function content()
    {
        if ($this->contentDeliveryController === null) {
            $this->contentDeliveryController = new ContentDeliveryController($this->getClient());
        }

        return $this->contentDeliveryController;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getAuthenticatedUrl(string $url)
    {
        $response = $this->getClient()->request('GET', $url);

        return $response->getBody()->getContents();
    }
}

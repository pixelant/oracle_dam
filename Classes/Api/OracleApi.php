<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Api;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\OAuth2Middleware;
use Oracle\Typo3Dam\Api\Controller\ContentManagementController;

class OracleApi
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $oceDomain;

    /**
     * @var string
     */
    protected $tokenDomain;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var ContentManagementController
     */
    protected $contentManagementController;

    /**
     * @var CachePolicy
     */
    protected $cachePolicy;

    /**
     * @var string|null
     */
    protected $scopeDomain;

    /**
     * @param string $oceDomain
     * @param string $tokenDomain
     * @param string $clientId
     * @param string $clientSecret
     * @param string|null $scopeDomain
     */
    public function __construct(
        string $oceDomain,
        string $tokenDomain,
        string $clientId,
        string $clientSecret,
        ?string $scopeDomain = null
    ) {
        $this->oceDomain = $oceDomain;
        $this->tokenDomain = $tokenDomain;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scopeDomain = $scopeDomain ?? $this->resolveScopeDomain();
    }

    /**
     * Returns the scope domain.
     *
     * This is the CNAME to which the oceDomain points. If no CNAME record exists, the oceDomain is returned, as it
     * logically follows that a domain without CNAME record cannot be resolved further and must be the scope domain.
     *
     * @return string
     */
    private function resolveScopeDomain(): string
    {
        return dns_get_record($this->oceDomain, DNS_CNAME)[0]['target'] ?? $this->oceDomain;
    }

    /**
     * Gets the Guzzle client instance used for making calls.
     *
     * @return Client
     */
    protected function getClient(): Client
    {
        if (!($this->client instanceof Client)) {
            $authorizationClient  = new Client([
                'base_uri' => 'https://' . $this->tokenDomain . '/oauth2/v1/token'
            ]);

            $authorizationConfiguration = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => 'https://' . $this->scopeDomain . ':443/urn:opc:cec:all',
            ];

            $grantType = new ClientCredentials($authorizationClient, $authorizationConfiguration);

            $oauth = new OAuth2Middleware($grantType);

            $stack = HandlerStack::create();

            $stack->push($oauth);

            $this->client = new Client([
                'base_uri' => 'https://' . $this->oceDomain,
                'handler' => $stack,
                'auth' => 'oauth',
            ]);
        }

        return $this->client;
    }

    /**
     * @return ContentManagementController
     */
    public function contentManagement(): ContentManagementController
    {
        if ($this->contentManagementController === null) {
            $this->contentManagementController = new ContentManagementController($this->getClient());
        }

        return $this->contentManagementController;
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function getAuthenticatedUrl(string $url): string
    {
        $response = $this->getClient()->request('GET', $url);

        return $response->getBody()->getContents();
    }
}

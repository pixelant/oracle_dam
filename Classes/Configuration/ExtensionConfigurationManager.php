<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

class ExtensionConfigurationManager implements SingletonInterface
{
    protected const JAVASCRIPT_UI_URL = 'https://static.ocecdn.oraclecloud.com/cdn/cec/api/oracle-ce-ui-2.11.js';

    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var string
     */
    protected $oceDomain;

    /**
     * @var string
     */
    protected $repositoryId;

    /**
     * @var string
     */
    protected $channelId;

    /**
     * @var string
     */
    protected $javaScriptUiUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $tokenDomain;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;

        $this->oceDomain = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_DOMAIN',
            'oceDomain'
        );

        $this->repositoryId = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_REPOSITORY',
            'repositoryId'
        );

        $this->channelId = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_CHANNEL',
            'channelId'
        );

        $this->javaScriptUiUrl = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_JS_URL',
            'jsUiUrl'
        ) ?: self::JAVASCRIPT_UI_URL;

        $this->clientId = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_CLIENT',
            'clientId'
        );

        $this->clientSecret = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_SECRET',
            'clientSecret'
        );

        $this->tokenDomain = $this->getFromEnvironmentOrExtensionConfiguration(
            'APP_ORACLE_DAM_TOKEN_DOMAIN',
            'tokenDomain'
        );
    }

    /**
     * @param string $environmentName
     * @param string $extensionConfigurationName
     * @return string
     */
    private function getFromEnvironmentOrExtensionConfiguration(
        string $environmentName,
        string $extensionConfigurationName
    ): string {
        return getenv($environmentName)
            ?: (string)($this->extensionConfiguration->get('oracle_dam')[$extensionConfigurationName] ?? '');
    }

    /**
     * @return string|null
     */
    public function getDownloadFolder(): ?string
    {
        return '1:user_upload/oracle';
    }

    /**
     * @return string
     */
    public function getOceDomain(): string
    {
        return $this->oceDomain;
    }

    /**
     * @return string
     */
    public function getRepositoryId(): string
    {
        return $this->repositoryId;
    }

    /**
     * @return string
     */
    public function getChannelId(): string
    {
        return $this->channelId;
    }

    /**
     * @return string
     */
    public function getJavaScriptUiUrl(): string
    {
        return $this->javaScriptUiUrl;
    }

    /**
     * Returns true if extension is sufficiently configured to work.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->oceDomain)
            && !empty($this->repositoryId)
            && !empty($this->channelId)
            && !empty($this->clientId)
            && !empty($this->clientSecret)
            && !empty($this->javaScriptUiUrl)
            && !empty($this->scope)
            && !empty($this->tokenDomain);
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @return string
     */
    public function getTokenDomain(): string
    {
        return $this->tokenDomain;
    }
}

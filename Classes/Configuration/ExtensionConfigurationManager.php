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
    protected $repositoryID;

    /**
     * @var string
     */
    protected $channelID;

    /**
     * @var string
     */
    protected $javaScriptUiUrl;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $configuration = $this->extensionConfiguration->get('oracle_dam');

        $this->oceDomain = getenv('APP_ORACLE_DAM_OCE_DOMAIN') ?: (string)$configuration['oceDomain'];
        $this->repositoryID = getenv('APP_ORACLE_DAM_REPOSITORY_ID') ?: (string)$configuration['repositoryID'];
        $this->channelID = getenv('APP_ORACLE_DAM_CHANNEL_ID') ?: (string)$configuration['channelID'];
        $this->javaScriptUiUrl = getenv('APP_ORACLE_DAM_JS_UI_URL')
            ?: (string)$configuration['jsUiUrl']
            ?: self::JAVASCRIPT_UI_URL;
    }

    /**
     * @return string|null
     */
    public static function getDownloadFolder(): ?string
    {
        return '1:user_upload/oracle_dam';
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
    public function getRepositoryID(): string
    {
        return $this->repositoryID;
    }

    /**
     * @return string
     */
    public function getChannelID(): string
    {
        return $this->channelID;
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
        return !empty($this->oceDomain) && !empty($this->repositoryID) && !empty($this->channelID);
    }
}

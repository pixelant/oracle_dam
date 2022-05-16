<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

class ExtensionConfigurationManager implements SingletonInterface
{
    /**
     * @var ExtensionConfiguration
     */
    protected $extensionConfiguration;

    /**
     * @var string
     */
    protected $oceUrl;

    /**
     * @var string
     */
    protected $repositoryID;

    /**
     * @var string
     */
    protected $channelID;

    /**
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extensionConfiguration = $extensionConfiguration;
        $configuration = $this->extensionConfiguration->get('oracle_dam');

        $this->oceUrl = getenv('APP_ORACLE_DAM_OCE_URL') ?: (string)$configuration['oceUrl'];
        $this->repositoryID = getenv('APP_ORACLE_DAM_REPOSITORY_ID') ?: (string)$configuration['repositoryID'];
        $this->channelID = getenv('APP_ORACLE_DAM_CHANNEL_ID') ?: (string)$configuration['channelID'];
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
    public function getOceUrl(): string
    {
        return $this->oceUrl;
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
     * Returns true if extension is sufficiently configured to work.
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->oceUrl) && !empty($this->repositoryID) && !empty($this->channelID);
    }
}

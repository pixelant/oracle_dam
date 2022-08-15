<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Domain\Repository;

use Oracle\Typo3Dam\Api\OracleApi;
use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use TYPO3\CMS\Core\SingletonInterface;

abstract class AbstractOracleDamRepository implements SingletonInterface
{
    /**
     * @var OracleApi
     */
    protected $api;

    /**
     * @param ExtensionConfigurationManager $configuration
     */
    public function __construct(ExtensionConfigurationManager $configuration, OracleApi $oracleApi = null)
    {
        $this->api = $oracleApi ?? $this->getOracleApi($configuration);
    }

    /**
     * @param ExtensionConfigurationManager $configuration
     * @return OracleApi
     */
    protected function getOracleApi(ExtensionConfigurationManager $configuration): OracleApi
    {
        return new OracleApi(
            $configuration->getOceDomain(),
            $configuration->getTokenDomain(),
            $configuration->getClientId(),
            $configuration->getClientSecret(),
            $configuration->getScopeDomain()
        );
    }
}

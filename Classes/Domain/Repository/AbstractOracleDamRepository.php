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
    public function __construct(ExtensionConfigurationManager $configuration)
    {
        $this->api = new OracleApi(
            'https://' . $configuration->getOceDomain(),
            'https://' . $configuration->getTokenDomain(),
            $configuration->getScope(),
            $configuration->getClientId(),
            $configuration->getClientSecret()
        );
    }
}

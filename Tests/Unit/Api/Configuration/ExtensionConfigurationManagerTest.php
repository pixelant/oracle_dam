<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Tests\Unit\Api\Configuration;

use Oracle\Typo3Dam\Configuration\ExtensionConfigurationManager;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class ExtensionConfigurationManagerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function configureWithEnvironmentVariables(): void
    {
        $values = [
            'APP_ORACLE_DAM_DOMAIN' => 'theDomainFromEnvironment',
            'APP_ORACLE_DAM_REPOSITORY' => 'theRepositoryFromEnvironment',
            'APP_ORACLE_DAM_CHANNEL' => 'theChannelFromEnvironment',
            'APP_ORACLE_DAM_JS_URL' => 'theJsUrlFromEnvironment',
            'APP_ORACLE_DAM_CLIENT' => 'theClientFromEnvironment',
            'APP_ORACLE_DAM_SECRET' => 'theSecretFromEnvironment',
            'APP_ORACLE_DAM_SCOPE' => 'theScopeFromEnvironment',
            'APP_ORACLE_DAM_TOKEN_DOMAIN' => 'theTokenDomainFromEnvironment',
        ];

        foreach ($values as $key => $value) {
            putenv($key . '=' . $value);
        }

        $subject = new ExtensionConfigurationManager(self::createMock(ExtensionConfiguration::class));

        self::assertEquals(
            $values['APP_ORACLE_DAM_DOMAIN'],
            $subject->getOceDomain(),
            'getOceDomain() returns value of environment variable APP_ORACLE_DAM_DOMAIN'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_REPOSITORY'],
            $subject->getRepositoryId(),
            'getRepositoryId() returns value of environment variable APP_ORACLE_DAM_REPOSITORY'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_CHANNEL'],
            $subject->getChannelId(),
            'getChannelId() returns value of environment variable APP_ORACLE_DAM_CHANNEL'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_JS_URL'],
            $subject->getJavaScriptUiUrl(),
            'getJavaScriptUiUrl() returns value of environment variable APP_ORACLE_DAM_JS_URL'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_CLIENT'],
            $subject->getClientId(),
            'getClientId() returns value of environment variable APP_ORACLE_DAM_CLIENT'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_SECRET'],
            $subject->getClientSecret(),
            'getClientSecret() returns value of environment variable APP_ORACLE_DAM_SECRET'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_SCOPE'],
            $subject->getScope(),
            'getScope() returns value of environment variable APP_ORACLE_DAM_SCOPE'
        );

        self::assertEquals(
            $values['APP_ORACLE_DAM_TOKEN_DOMAIN'],
            $subject->getTokenDomain(),
            'getTokenDomain() returns value of environment variable APP_ORACLE_DAM_TOKEN_DOMAIN'
        );

        foreach ($values as $key => $value) {
            putenv($key);
        }
    }

    /**
     * @test
     */
    public function configureWithExtensionConfiguration(): void
    {
        $values = [
            'oceDomain' => 'theDomainFromExtensionConfig',
            'repositoryId' => 'theRepositoryFromExtensionConfig',
            'channelId' => 'theChannelFromExtensionConfig',
            'jsUiUrl' => 'theJsUrlFromExtensionConfig',
            'clientId' => 'theClientFromExtensionConfig',
            'clientSecret' => 'theSecretFromExtensionConfig',
            'scope' => 'theScopeFromExtensionConfig',
            'tokenDomain' => 'theTokenDomainFromExtensionConfig',
        ];

        GeneralUtility::addInstance(ConfigurationManager::class, self::createMock(ConfigurationManager::class));

        $extensionConfiguration = new ExtensionConfiguration();

        $extensionConfiguration->set('oracle_dam', $values);

        $subject = new ExtensionConfigurationManager($extensionConfiguration);

        self::assertEquals(
            $values['oceDomain'],
            $subject->getOceDomain(),
            'getOceDomain() returns value of extension configuration property oceDomain'
        );

        self::assertEquals(
            $values['repositoryId'],
            $subject->getRepositoryId(),
            'getRepositoryId() returns value of extension configuration property repositoryId'
        );

        self::assertEquals(
            $values['channelId'],
            $subject->getChannelId(),
            'getChannelId() returns value of extension configuration property channelId'
        );

        self::assertEquals(
            $values['jsUiUrl'],
            $subject->getJavaScriptUiUrl(),
            'getJavaScriptUiUrl() returns value of extension configuration property jsUiUrl'
        );

        self::assertEquals(
            $values['clientId'],
            $subject->getClientId(),
            'getClientId() returns value of extension configuration property clientId'
        );

        self::assertEquals(
            $values['clientSecret'],
            $subject->getClientSecret(),
            'getClientSecret() returns value of extension configuration property clientSecret'
        );

        self::assertEquals(
            $values['scope'],
            $subject->getScope(),
            'getScope() returns value of extension configuration property scope'
        );

        self::assertEquals(
            $values['tokenDomain'],
            $subject->getTokenDomain(),
            'getTokenDomain() returns value of extension configuration property tokenDomain'
        );
    }

    /**
     * @test
     */
    public function isConfiguredChecksAllProperties(): void
    {
        $values = [
            'APP_ORACLE_DAM_DOMAIN' => 'theDomainFromEnvironment',
            'APP_ORACLE_DAM_REPOSITORY' => 'theRepositoryFromEnvironment',
            'APP_ORACLE_DAM_CHANNEL' => 'theChannelFromEnvironment',
            'APP_ORACLE_DAM_JS_URL' => 'theJsUrlFromEnvironment',
            'APP_ORACLE_DAM_CLIENT' => 'theClientFromEnvironment',
            'APP_ORACLE_DAM_SECRET' => 'theSecretFromEnvironment',
            'APP_ORACLE_DAM_SCOPE' => 'theScopeFromEnvironment',
            'APP_ORACLE_DAM_TOKEN_DOMAIN' => 'theTokenDomainFromEnvironment',
        ];

        foreach ($values as $key => $value) {
            putenv($key . '=' . $value);
        }

        $extensionConfigurationMock = self::createMock(ExtensionConfiguration::class);

        $extensionConfigurationMock
            ->method('get')
            ->willReturn('');

        $subject = new ExtensionConfigurationManager($extensionConfigurationMock);

        self::assertTrue(
            $subject->isConfigured(),
            'isConfigured() returns true when all properties are set'
        );

        foreach ($values as $key => $value) {
            putenv($key);
        }

        // Is set by constant so it will always be set.
        unset($values['APP_ORACLE_DAM_JS_URL']);

        foreach (array_keys($values) as $keyToSkip) {
            foreach ($values as $key => $value) {
                if ($key === $keyToSkip) {
                    continue;
                }

                putenv($key . '=' . $value);
            }

            $subject = new ExtensionConfigurationManager($extensionConfigurationMock);

            self::assertFalse(
                $subject->isConfigured(),
                'isConfigured() returns false when ' . $keyToSkip . ' is not set'
            );

            foreach ($values as $key => $value) {
                putenv($key);
            }
        }
    }
}

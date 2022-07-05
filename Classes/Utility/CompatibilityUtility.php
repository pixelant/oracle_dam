<?php

declare(strict_types=1);

namespace Oracle\Typo3Dam\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Miscellaneous functions relating to compatibility with different TYPO3 versions
 */
class CompatibilityUtility
{
    /**
     * Returns true if the current TYPO3 version is less than $version
     *
     * @param string $version
     * @return bool
     */
    public static function typo3VersionIsLessThan(string $version): bool
    {
        return self::getTypo3VersionInteger() < VersionNumberUtility::convertVersionNumberToInteger($version);
    }

    /**
     * Returns true if the current TYPO3 version is less than or equal to $version
     *
     * @param string $version
     * @return bool
     */
    public static function typo3VersionIsLessThanOrEqualTo(string $version): bool
    {
        return self::getTypo3VersionInteger() <= VersionNumberUtility::convertVersionNumberToInteger($version);
    }

    /**
     * Returns true if the current TYPO3 version is greater than $version
     *
     * @param string $version
     * @return bool
     */
    public static function typo3VersionIsGreaterThan(string $version): bool
    {
        return self::getTypo3VersionInteger() > VersionNumberUtility::convertVersionNumberToInteger($version);
    }

    /**
     * Returns true if the current TYPO3 version is greater than or equal to $version
     *
     * @param string $version
     * @return bool
     */
    public static function typo3VersionIsGreaterThanOrEqualTo(string $version): bool
    {
        return self::getTypo3VersionInteger() >= VersionNumberUtility::convertVersionNumberToInteger($version);
    }

    /**
     * Returns the TYPO3 version as an integer
     *
     * @return int
     */
    public static function getTypo3VersionInteger(): int
    {
        return VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version());
    }

    /**
     * Add multiple values to the extension configuration.
     *
     * @param ExtensionConfiguration $extensionConfiguration
     * @param string $extension
     * @param array $values
     */
    public static function setMultipleExtensionConfigurations(
        ExtensionConfiguration $extensionConfiguration,
        string $extension,
        array $values
    ): void {
        if (self::typo3VersionIsLessThan('11')) {
            foreach ($values as $key => $value) {
                $extensionConfiguration->set($extension, $key, $value);
            }

            return;
        }

        $extensionConfiguration->set($extension, $values);
    }
}

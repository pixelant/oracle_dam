<?php

defined('TYPO3_MODE') or die('Access denied.');

(static function (): void {
    // Add the button
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1652700576248] = [
        'nodeName' => 'inline',
        'priority' => 50,
        'class' => \Oracle\Typo3Dam\FormEngine\Container\SelectorButtonContainer::class,
    ];

    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 11000000) {
        return;
    }

    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    );

    $iconRegistry->registerIcon(
        'tx-oracle-logo',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:oracle_dam/Resources/Public/Icons/Oracle.svg']
    );
})();

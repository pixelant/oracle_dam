<?php

defined('TYPO3_MODE') || die('Access denied.');

(function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'file',
        'oracleDamAssetList',
        '',
        null,
        [
            'navigationComponentId' => '',
            'inheritNavigationComponentFromMainModule' => false,
            'routeTarget' => \Oracle\Typo3Dam\Controller\AssetListModuleController::class . '::handleRequest',
            'access' => 'user,group',
            'name' => 'file_oracleDamAssetList',
            'iconIdentifier' => 'tx-oracle-module',
            'labels' => 'LLL:EXT:oracle_dam/Resources/Private/Language/Modules/asset_list.xlf',
        ]
    );
})();

<?php

defined('TYPO3_MODE') or die('Access denied.');

(static function (): void {
    // Add the button
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1652700576248] = [
        'nodeName' => 'inline',
        'priority' => 50,
        'class' => \Oracle\Typo3Dam\FormEngine\Container\SelectorButtonContainer::class,
    ];
})();

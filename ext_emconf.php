<?php
// phpcs:ignoreFile

$EM_CONF['oracle_dam'] = [
    'title' => 'Oracle Content Management DAM',
    'description' => 'Select and import assets from the Oracle Content Management (OCM) Digital Asset Management (DAM) system.',
    'version' => '0.1.0',
    'category' => 'module',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.1.99',
            'typo3' => '10.4.0-11.5.99',
        ],
    ],
    'state' => 'beta',
    'uploadfolder' => false,
    'createDirs' => '',
    'author' => 'Pixelant.net for Oracle',
    'author_email' => 'info@pixelant.net',
    'author_company' => 'Pixelant.net for Oracle',
    'autoload' => [
        'psr-4' => [
            'Oracle\\Typo3Dam\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'Oracle\\Typo3Dam\\Tests\\' => 'Tests/',
        ],
    ],
];

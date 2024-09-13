<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ai_file_uploads'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'autoincrement' => true, 'notnull' => true, 'unsigned' => true]
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ],
        'name' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'purpose' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ],
        'file' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'file_id' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ]
    ]
];
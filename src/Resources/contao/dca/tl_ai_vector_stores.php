<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ai_vector_stores'] = [
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
        'file_ids' => [
            'sql' => 'blob NULL'
        ],
        'vector_store_id' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ],
        'state' => [
            'sql' => ['type' => 'string', 'length' => 64, 'default' => '']
        ]
    ]
];
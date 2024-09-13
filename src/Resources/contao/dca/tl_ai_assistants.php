<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ai_assistants'] = [
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
        'description' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'instructions' => [
            'sql' => 'text NULL'
        ],
        'assistant_id' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ],
        'vector_stores' => [
            'sql' => 'blob NULL'
        ]
    ]
];
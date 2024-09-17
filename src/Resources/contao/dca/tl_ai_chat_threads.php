<?php

use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_ai_chat_threads'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_ai_agents',
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
        'pid' => [
            'sql' => ['type' => 'integer', 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ],
        'name' => [
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'thread_id' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ],
        'last_prompt' => [
            'sql' => 'text NULL'
        ],
        'last_run_id' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ]
    ]
];
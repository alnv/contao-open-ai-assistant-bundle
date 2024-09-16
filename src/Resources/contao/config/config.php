<?php

use Contao\ArrayUtil;

ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 2, [
    'ai-bundle' => [
        'assistants' => [
            'name' => 'assistants',
            'tables' => [
                'tl_ai_assistants'
            ]
        ],
        'file_uploads' => [
            'name' => 'file_uploads',
            'tables' => [
                'tl_ai_file_uploads'
            ]
        ],
        'vector_stores' => [
            'name' => 'vector_stores',
            'tables' => [
                'tl_ai_vector_stores'
            ]
        ]
    ]
]);
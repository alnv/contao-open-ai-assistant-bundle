<?php

use Contao\ArrayUtil;
use Alnv\ContaoOpenAiAssistantBundle\Modules\AiChatBotModule;

$GLOBALS['OPEN_AI_MESSAGE_PARSER'] = $GLOBALS['OPEN_AI_MESSAGE_PARSER']  ?? [];

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

ArrayUtil::arrayInsert($GLOBALS['FE_MOD'], 2, [
    'open-ai-bundle' => [
        'ai_chat_bot' => AiChatBotModule::class
    ]
]);
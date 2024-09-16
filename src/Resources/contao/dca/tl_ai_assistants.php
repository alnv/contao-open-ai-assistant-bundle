<?php

use Contao\DC_Table;
use Contao\Database;
use Contao\DataContainer;
use Alnv\ContaoOpenAiAssistantBundle\Library\Assistant;

$GLOBALS['TL_DCA']['tl_ai_assistants'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ondelete_callback' => [function (DataContainer $objDataContainer) {
            if (!$objDataContainer->id) {
                return;
            }
            $objAssistantEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_assistants WHERE id=?')->limit(1)->execute($objDataContainer->id);
            if (!$objAssistantEntity->numRows) {
                return;
            }
            if (!$objAssistantEntity->assistant_id || !$objAssistantEntity->name) {
                return;
            }

            $objAssistant = new Assistant($objAssistantEntity->name);
            $objAssistant->deleteAssistantId();
        }],
        'onsubmit_callback' => [function (DataContainer $objDataContainer) {
            if (!$objDataContainer->id) {
                return;
            }
            $objAssistantEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_assistants WHERE id=?')->limit(1)->execute($objDataContainer->id);
            if (!$objAssistantEntity->numRows) {
                return;
            }
            if (!$objAssistantEntity->name) {
                return;
            }

            $objAssistant = new Assistant($objAssistantEntity->name);
            if (!$objAssistantEntity->assistant_id) {
                $objAssistant->createAssistantId();
            } else {
                $objAssistant->modifyAssistantId([
                    'name' => $objAssistantEntity->name ?: '',
                    'description' => $objAssistantEntity->description ?: '',
                    'instructions' => $objAssistantEntity->instructions ?: ''
                ]);
            }
        }],
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'panelLayout' => 'filter;sort,search',
            'fields' => ['name']
        ],
        'label' => [
            'fields' => ['name', 'description', 'assistant_id'],
            'showColumns' => true
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.svg'
            ]
        ]
    ],
    'palettes' => [
        'default' => 'name,description;instructions'
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'autoincrement' => true, 'notnull' => true, 'unsigned' => true]
        ],
        'tstamp' => [
            'flag' => 6,
            'sql' => ['type' => 'integer', 'notnull' => false, 'unsigned' => true, 'default' => 0]
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => [
                'mandatory' => true,
                'unique' => true,
                'maxlength' => 255,
                'doNotCopy' => true,
                'tl_class' => 'w50',
                'decodeEntities' => true
            ],
            'search' => true,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'description' => [
            'inputType' => 'textarea',
            'eval' => [
                'maxlength' => 255,
                'doNotCopy' => true,
                'tl_class' => 'clr',
                'decodeEntities' => true
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'instructions' => [
            'inputType' => 'textarea',
            'eval' => [
                'doNotCopy' => true,
                'tl_class' => 'clr',
                'decodeEntities' => true
            ],
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
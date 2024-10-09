<?php

use Alnv\ContaoOpenAiAssistantBundle\Library\VectorStore;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Contao\Environment;


$GLOBALS['TL_DCA']['tl_ai_vector_stores'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'onload_callback' => [
            function (DataContainer $objDataContainer) {
                $strVectorStoreId = $objDataContainer->id ?: Input::get('id');

                if (!Input::get('vector_store')) {
                    return;
                }

                $objVectorStoreEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores WHERE id=?')->limit(1)->execute($strVectorStoreId);
                if (!$objVectorStoreEntity->numRows) {
                    return;
                }

                if (Input::get('vector_store') == 'retrieve') {
                    $objVectorStore = new VectorStore($objVectorStoreEntity->name);
                    $objVectorStore->retrieve();
                }

                Controller::redirect(preg_replace('/&(amp;)?vector_store=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote(Input::get('vector_store'), '/') . '=[^&]*/i', '', Environment::get('request'))));
            },
            function (DataContainer $objDataContainer) {
                $strId = $objDataContainer->id;

                $strAct = Input::get('act') ?: '';
                if ($strAct != 'edit') {
                    return;
                }

                $objFileUploadEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores WHERE id=?')->limit(1)->execute($strId);
                if ($objFileUploadEntity->vector_store_id) {
                    $GLOBALS['TL_DCA']['tl_ai_vector_stores']['fields']['name']['eval']['readonly'] = true;
                    $GLOBALS['TL_DCA']['tl_ai_vector_stores']['fields']['file_ids']['eval']['disabled'] = true;
                }
            }
        ],
        'ondelete_callback' => [function (DataContainer $objDataContainer) {
            if (!$objDataContainer->id) {
                return;
            }
            $objVectorStoreEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores WHERE id=?')->limit(1)->execute($objDataContainer->id);
            if (!$objVectorStoreEntity->numRows) {
                return;
            }
            if (!$objVectorStoreEntity->vector_store_id || !$objVectorStoreEntity->name) {
                return;
            }

            try {
                $objVectorStore = new VectorStore($objVectorStoreEntity->name);
                $objVectorStore->deleteVectorStoreId();
            } catch (\Exception $objError) {}
        }],
        'onsubmit_callback' => [function (DataContainer $objDataContainer) {
            if (!$objDataContainer->id) {
                return;
            }
            $objVectorStoreEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores WHERE id=?')->limit(1)->execute($objDataContainer->id);
            if (!$objVectorStoreEntity->numRows) {
                return;
            }
            if (!$objVectorStoreEntity->name) {
                return;
            }

            $objVectorStore = new VectorStore($objVectorStoreEntity->name);
            $objVectorStore->createVectorStoreId();
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
            'fields' => ['name', 'vector_store_id', 'state', 'file_ids'],
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
            ],
            'retrieve' => [
                'icon' => 'sync.svg',
                'href' => 'vector_store=retrieve',
                'attributes' => 'onclick="if(!confirm(\'Soll der aktuelle Status abgerufen werden?\'))return false;Backend.getScrollOffset()"'
            ]
        ]
    ],
    'palettes' => [
        'default' => 'name;file_ids'
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
        'file_ids' => [
            'inputType' => 'checkboxWizard',
            'eval' => [
                'multiple' => true,
                'mandatory' => true,
                'tl_class' => 'clr'
            ],
            'options_callback' => function () {
                $arrFileIds = [];
                $objFileUploads = Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads ORDER BY `name`')->execute();
                while ($objFileUploads->next()) {
                    if (!$objFileUploads->file_id) {
                        continue;
                    }
                    $arrFileIds[$objFileUploads->file_id] = $objFileUploads->name;
                }
                return $arrFileIds;
            },
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
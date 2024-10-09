<?php

use Contao\Input;
use Contao\DC_Table;
use Contao\Database;
use Contao\DataContainer;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Alnv\ContaoOpenAiAssistantBundle\Library\FileUpload;

$GLOBALS['TL_DCA']['tl_ai_file_uploads'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'onload_callback' => [function (DataContainer $objDataContainer) {
            $strId = $objDataContainer->id;
            $strAct = Input::get('act') ?: '';

            if ($strAct != 'edit') {
                return;
            }

            $objFileUploadEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads WHERE id=?')->limit(1)->execute($strId);
            if ($objFileUploadEntity->file_id) {
                $GLOBALS['TL_DCA']['tl_ai_file_uploads']['fields']['name']['eval']['readonly'] = true;
                PaletteManipulator::create()->removeField('file')->applyToPalette('default', 'tl_ai_file_uploads');
            }
        }],
        'ondelete_callback' => [function (DataContainer $objDataContainer) {
            if (!$objDataContainer->id) {
                return;
            }

            $objFileUploadEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads WHERE id=?')->limit(1)->execute($objDataContainer->id);
            if (!$objFileUploadEntity->numRows) {
                return;
            }
            if (!$objFileUploadEntity->file_id || !$objFileUploadEntity->name) {
                return;
            }

            try {
                $objFileUpload = new FileUpload($objFileUploadEntity->name);
                $objFileUpload->deleteFileId();
            } catch (\Exception $objError) {}
        }],
        'onsubmit_callback' => [function (DataContainer $objDataContainer) {
            if (!$objDataContainer->id) {
                return;
            }

            $objFileUploadEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads WHERE id=?')->limit(1)->execute($objDataContainer->id);
            if (!$objFileUploadEntity->numRows) {
                return;
            }
            if (!$objFileUploadEntity->name) {
                return;
            }

            $objFileUpload = new FileUpload($objFileUploadEntity->name);
            $objFileUpload->uploadFile();
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
            'fields' => ['name', 'file_id'],
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
        'default' => 'name;file'
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
                'unique' => true,
                'maxlength' => 255,
                'doNotCopy' => true,
                'tl_class' => 'w50',
                'mandatory' => true,
                'decodeEntities' => true
            ],
            'search' => true,
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'purpose' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => 'assistants']
        ],
        'file' => [
            'inputType' => 'fileTree',
            'eval' => [
                'mandatory' => true,
                'fieldType' => 'radio',
                'tl_class' => 'clr',
                'filesOnly' => true,
                'extensions' => 'txt'
            ],
            'sql' => 'blob NULL'
        ],
        'file_id' => [
            'sql' => ['type' => 'string', 'length' => 128, 'default' => '']
        ]
    ]
];
<?php

use Alnv\ContaoOpenAiAssistantBundle\Helpers\Getters;

$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'aiToggleButton';
$GLOBALS['TL_DCA']['tl_module']['palettes']['ai_chat_bot'] = '{title_legend},name,headline,type;{open_ai_legend},aiAssistant,aiAssistantName,aiParser,aiQuestionSuggestions,aiToggleButton;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['aiToggleButton'] = 'aiToggleMode';

$GLOBALS['TL_DCA']['tl_module']['fields']['aiAssistantName'] = [
    'inputType' => 'text',
    'eval' => [
        'maxlength' => 64,
        'tl_class' => 'w50',
        'mandatory' => false
    ],
    'sql' => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['aiAssistant'] = [
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 128,
        'tl_class' => 'w50',
        'mandatory' => true,
        'includeBlankOption' => true,
    ],
    'options_callback' => function () {
        $arrAssistants = [];
        foreach (Getters::getAssistants() as $arrAssistant) {
            if (!($arrAssistant['name'] ?? '')) {
                continue;
            }
            $arrAssistants[] = $arrAssistant['name'];
        }
        return $arrAssistants;
    },
    'sql' => "varchar(128) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['aiParser'] = [
    'inputType' => 'select',
    'eval' => [
        'chosen' => true,
        'maxlength' => 64,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
    ],
    'options_callback' => function () {
        $arrParser = [];
        foreach ($GLOBALS['OPEN_AI_MESSAGE_PARSER'] as $strName => $arrValue) {
            $arrParser[$strName] = $arrValue['label'] ?? $strName;
        }
        return $arrParser;
    },
    'sql' => "varchar(64) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['aiQuestionSuggestions'] = [
    'inputType' => 'listWizard',
    'eval' => [
        'tl_class' => 'clr'
    ],
    'sql' => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['aiToggleButton'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
        'submitOnChange' => true
    ],
    'sql' => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_module']['fields']['aiToggleMode'] = [
    'inputType' => 'select',
    'eval' => [
        'maxlength' => 32,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
    ],
    'options_callback' => function () {
        return ['hiddenDefault'];
    },
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['aiToggleModeOptions'],
    'sql' => "varchar(64) NOT NULL default ''"
];
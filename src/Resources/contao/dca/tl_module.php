<?php

use Alnv\ContaoOpenAiAssistantBundle\Helpers\Getters;

$GLOBALS['TL_DCA']['tl_module']['palettes']['ai_chat_bot'] = '{title_legend},name,headline,type;{open_ai_legend},aiAssistant,aiParser;{protected_legend:hide:hide},protected;{expert_legend:hide},guests,cssID,space';

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
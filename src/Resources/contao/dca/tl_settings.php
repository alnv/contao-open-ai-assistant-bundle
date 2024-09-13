<?php

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{openai_settings},openaiApi';

$GLOBALS['TL_DCA']['tl_settings']['fields']['openaiApi'] = [
    'inputType' => 'text',
    'eval' => [
        'tl_class' => 'w50'
    ]
];
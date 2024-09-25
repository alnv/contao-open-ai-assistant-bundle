<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Modules;

use Alnv\ContaoOpenAiAssistantBundle\Components\AiSearchComponent;
use Alnv\ContaoOpenAiAssistantBundle\Helpers\Getters;
use Contao\BackendTemplate;
use Contao\Module;
use Contao\System;

class AiChatBotModule extends Module
{

    protected $strTemplate = 'mod_ai_chat_bot';

    public function generate(): string
    {

        if (System::getContainer()->get('request_stack')->getCurrentRequest()->get('_scope') == 'backend') {

            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . \strtoupper($GLOBALS['TL_LANG']['FMD']['ai_chat_bot'][0] ?? '') . ' ###';

            return $objTemplate->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {

        $arrOptions = [
            'assistant' => $this->aiAssistant
        ];

        $strParserClass = Getters::getParserClassNameByName($this->aiParser ?: '');
        if  ($strParserClass && class_exists($strParserClass)) {
            $objParser = new $strParserClass();
            $arrOptions['additional_instructions'] = $objParser->getAdditionalInstructions();
            $arrOptions['parser'] = [$strParserClass, 'parseMessages'];
        }

        $this->Template->chat = (new AiSearchComponent($arrOptions))->generate();
    }
}
<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Components;

use Alnv\ContaoOpenAiAssistantBundle\Helpers\Toolkit;
use Contao\Combiner;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Michelf\MarkdownExtra;

class AiChatComponent
{

    protected array $arrOptions;

    public function __construct($arrOptions = [])
    {
        $this->arrOptions = $arrOptions;
    }

    public function generate(): string
    {

        $this->setResources();

        $objTemplate = new FrontendTemplate('ai_chat_component');

        $blnUseToggle = $this->arrOptions['toggle'] ?? false;
        if (!$blnUseToggle) {
            $this->arrOptions['toggle_mode'] = '';
        }

        $arrTemplateData = [
            'postOptions' => Toolkit::array2Base64($this->arrOptions),
            'assistant' => $this->arrOptions['assistant'] ?? '',
            'toggle' => $blnUseToggle,
            'toggle_mode' => $this->arrOptions['toggle_mode'] ?? '',
            'question_suggestions' => $this->arrOptions['question_suggestions'] ?? [],
            'templateId' => 'id_' . uniqid()
        ];

        $arrTemplateData['script'] = $this->getScript($arrTemplateData);
        $objTemplate->setData($arrTemplateData);

        return $objTemplate->parse();
    }

    public function parseMessages(array $arrMessages): array
    {

        $arrResults = [];
        foreach (($arrMessages['data'] ?? []) as $arrMessageData) {

            $strMessage = "";
            foreach ($arrMessageData['content'] as $arrContent) {

                if ($arrContent['type'] != 'text') {
                    continue;
                }

                $strText = $arrContent['text']['value'] ?? '';
                $strText = StringUtil::decodeEntities($strText);

                $objParser = new MarkdownExtra;
                $strMessage .= $objParser->transform($strText);
            }

            if (isset($this->arrOptions['parser']) && is_array($this->arrOptions['parser'])) {
                $objParser = new $this->arrOptions['parser'][0]();
                $strMessage = $objParser->{$this->arrOptions['parser'][1]}($strMessage, $arrMessages);
            }

            $strName = $arrMessageData['role'] == 'assistant' ? ($this->arrOptions['assistant_name'] ?? $arrMessageData['role']) : $arrMessageData['role'];
            $strMessage = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strMessage);

            if ($strMessage) {
                $arrResults[] = [
                    'name' => $strName,
                    'role' => $arrMessageData['role'],
                    'message' => $strMessage
                ];
            }
        }

        return $arrResults;
    }

    protected function getScript($arrTemplateData): string
    {

        $objTemplate = new FrontendTemplate('j_ai_chat_component');
        $objTemplate->setData($arrTemplateData);

        return $objTemplate->parse();
    }

    protected function setResources(): void
    {

        if (!isset($GLOBALS['TL_HEAD']['vue'])) {

            $objCombiner = new Combiner();
            $objCombiner->add('/bundles/alnvcontaoopenaiassistant/js/vue.min.js');
            $objCombiner->add('/bundles/alnvcontaoopenaiassistant/js/vue-resource.min.js');
            $GLOBALS['TL_HEAD']['vue'] = '<script src="' . $objCombiner->getCombinedFile() . '"></script>';
        }
    }
}
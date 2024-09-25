<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Components;

use Alnv\ContaoOpenAiAssistantBundle\Helpers\Toolkit;
use Contao\Combiner;
use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use Michelf\MarkdownExtra;

class AiSearchComponent
{

    protected array $arrOptions;

    public function __construct($arrOptions = [])
    {
        $this->arrOptions = $arrOptions;
    }

    public function generate(): string
    {

        $this->setResources();

        $objTemplate = new FrontendTemplate('ai_search_component');

        $arrTemplateData = [
            'postOptions' => Toolkit::array2Base64($this->arrOptions),
            'assistant' => $this->arrOptions['assistant'] ?? '',
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

            $strMessage = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strMessage);

            if ($strMessage) {
                $arrResults[] = [
                    'role' => $arrMessageData['role'] ?? '',
                    'message' => $strMessage
                ];
            }
        }

        return $arrResults;

        /*
        $arrResults = [];
        $arrMessage = $arrMessages['data'][0] ?? [];
        $strRole = $arrMessage['role'] ?? '';

        if ($strRole !== 'assistant') {
            return [];
        }

        $strMessage = "";
        foreach ($arrMessage['content'] as $arrContent) {

            if ($arrContent['type'] != 'text') {
                continue;
            }

            $strText = $arrContent['text']['value'] ?? '';

            if (!$strText) {
                continue;
            }

            $objMarkdown = Markdown::new();
            $objMarkdown->setContent($strText);
            $strMessage .= $objMarkdown->toHtml();
        }

        if (isset($this->arrOptions['parser']) && is_array($this->arrOptions['parser'])) {
            $objParser = new $this->arrOptions['parser'][0]();
            $strMessage = $objParser->{$this->arrOptions['parser'][1]}($strMessage, $arrMessages);
        }

        $strMessage = System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strMessage);

        if ($strMessage) {
            $arrResults[] = [
                'role' => 'assistant',
                'message' => $strMessage
            ];
        }

        return $arrResults;
        */
    }

    protected function getScript($arrTemplateData): string
    {

        $objTemplate = new FrontendTemplate('j_ai_search_component');
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
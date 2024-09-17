<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Helpers;

class Toolkit
{

    public static function getJsonFromMessage($strContent, $strStartTag, $strEndTag): array
    {

        $ini = strpos($strContent, $strStartTag);

        if ($ini == 0) {
            return [];
        }

        $ini += strlen($strStartTag);
        $len = strpos($strContent, $strEndTag, $ini) - $ini;
        $strContent = substr($strContent, $ini, $len);
        $strContent = str_replace(["\r", "\n"], '', $strContent);

        return json_decode(str_replace(' ', '', $strContent), true);
    }
}
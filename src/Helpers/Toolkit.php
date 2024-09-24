<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Helpers;

class Toolkit
{

    public static function getJsonFromMessage($strContent, $strStartTag, $strEndTag): array
    {

        $ini = strpos($strContent, $strStartTag);

        if ($ini === 0) {
            return [];
        }

        $ini += strlen($strStartTag);
        $len = strpos($strContent, $strEndTag, $ini) - $ini;
        $strContent = substr($strContent, $ini, $len);
        $strContent = str_replace(["\r", "\n"], '', $strContent);

        $varJson = json_decode(str_replace(' ', '', $strContent), true);

        return is_array($varJson) ? $varJson : [];
    }

    public static function replace($strContent, $strStartTag, $strEndTag, $strReplace): string
    {

        $ini = strpos($strContent, $strStartTag);

        if ($ini === 0) {
            return '';
        }

        $strFound = $strContent;

        $ini += strlen($strStartTag);
        $len = strpos($strFound, $strEndTag, $ini) - $ini;

        $strFound = substr($strFound, $ini, $len);

        return str_replace($strFound, $strReplace, $strContent);
    }

    public static function array2Base64($arrArray): string
    {
        return \rtrim(\strtr(\base64_encode(\serialize($arrArray)), '+/', '-_'), '=');
    }

    public static function getSerializedArrayFromBase64($strBase64ArrayString): string
    {
        return \base64_decode(\str_pad(\strtr($strBase64ArrayString, '-_', '+/'), \strlen($strBase64ArrayString) % 4, '=', STR_PAD_RIGHT));
    }
}
<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Library;

abstract class Parser
{

    public function getAdditionalInstructions(): string
    {
        return '';
    }

    public function parseMessages($strMessage, $arrMessages): string
    {
        return '';
    }
}
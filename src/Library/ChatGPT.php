<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Library;

use Contao\Config;

abstract class ChatGPT
{

    protected string $strToken;

    public function __construct()
    {
        $this->strToken = Config::get('openaiApi') ?: '';
    }

    protected function getToken(): string
    {
        return $this->strToken;
    }
}
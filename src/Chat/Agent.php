<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Chat;

use Alnv\ContaoOpenAiAssistantBundle\Helpers\Statics;
use Alnv\ContaoOpenAiAssistantBundle\Library\Assistant;
use Alnv\ContaoOpenAiAssistantBundle\Library\FileUpload;
use Alnv\ContaoOpenAiAssistantBundle\Library\VectorStore;
use Contao\Config;
use Contao\Database;
use Contao\StringUtil;

class Agent
{

    protected string $strName;

    private string $strToken;

    protected array $arrOptions; // thread_id, additional_instructions

    protected Assistant $objAssistant;

    protected string $strThreadId = '';

    public function __construct(string $strName, array $arrOptions = [])
    {

        $this->strToken = Config::get('openaiApi') ?: '';

        $this->strName = $strName;
        $this->arrOptions = $arrOptions;

        $this->objAssistant = new Assistant($this->strName);

        $this->load();
    }

    public function reset()
    {
        // todo
    }

    protected function load(): void
    {

        $strThreadId = $this->arrOptions['thread_id'] ?? '';

        if (!$strThreadId) {
            $strThreadId = $this->createThread();
        }

        if (!$strThreadId) {
            throw new \RuntimeException($this->strName . ': Thread could not be created.');
        }

        $this->strThreadId = $strThreadId;
        $arrAgent = $this->getCurrentAgentArray();

        if (empty($arrAgent)) {

            $arrSet = [
                'tstamp' => time(),
                'name' => $this->strName,
                'thread_id' => $strThreadId
            ];

            Database::getInstance()->prepare('INSERT INTO tl_ai_chat_threads %s')->set($arrSet)->execute();
        }
    }

    public function getThreadId(): string
    {
        return $this->strThreadId;
    }

    protected function createThread(): string
    {

        $objCurl = \curl_init(Statics::URL_CREATE_THREAD);

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $this->strToken",
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        \curl_setopt($objCurl, CURLOPT_POST, true);

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        return $arrResponse['id'] ?? '';
    }

    public function getCurrentAgentArray(): array
    {

        $strThreadId = $this->getThreadId();

        if (!$strThreadId) {
            return [];
        }

        return Database::getInstance()->prepare('SELECT * FROM tl_ai_chat_threads WHERE `thread_id`=?')->limit(1)->execute($strThreadId)->row();
    }

    public function addMessage($strPrompt): Agent
    {

        $strThreadId = $this->getThreadId();
        $arrAgent = $this->getCurrentAgentArray();

        $strPrompt = StringUtil::decodeEntities($strPrompt);
        $strLastPrompt = StringUtil::decodeEntities($arrAgent['last_prompt']);

        if ($strLastPrompt && $strPrompt == $strLastPrompt) {
            return $this;
        }

        $arrData = [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'text',
                    'text' => $strPrompt
                ]
            ]
        ];

        $objCurl = \curl_init(sprintf(Statics::URL_CREATE_THREAD_MESSAGE, $strThreadId));

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $this->strToken",
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);
        \curl_setopt($objCurl, CURLOPT_POST, true);
        \curl_setopt($objCurl, CURLOPT_POSTFIELDS, \json_encode($arrData));

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        $this->run();

        Database::getInstance()
            ->prepare('UPDATE tl_ai_chat_threads %s WHERE thread_id=?')
            ->set(['last_prompt' => $strPrompt])
            ->limit(1)
            ->execute($strThreadId);

        return $this;
    }

    public function retrieveRun(): array
    {

        $arrAgent = $this->getCurrentAgentArray();

        $strThreadId = $this->getThreadId();
        $strRunId = $arrAgent['last_run_id'] ?? '';

        if (!$strThreadId || !$strRunId) {
            return [];
        }

        $objCurl = \curl_init(sprintf(Statics::URL_RETRIEVE_RUN, $strThreadId, $strRunId));

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $this->strToken",
            "OpenAI-Beta: assistants=v2"
        ]);

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        \curl_close($objCurl);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        return $arrResponse;
    }

    public function run(): array
    {

        $strThreadId = $this->getThreadId();

        $arrData = [
            // 'stream' => true,
            'additional_instructions' => $this->arrOptions['additional_instructions'] ?? '',
            'assistant_id' => $this->objAssistant->getAssistantId()
        ];

        $objCurl = \curl_init(\sprintf(Statics::URL_RUN_THREAD, $strThreadId));

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $this->strToken",
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        \curl_setopt($objCurl, CURLOPT_POST, true);
        \curl_setopt($objCurl, CURLOPT_POSTFIELDS, \json_encode($arrData));

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        \curl_close($objCurl);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        Database::getInstance()
            ->prepare('UPDATE tl_ai_chat_threads %s WHERE thread_id=?')
            ->set(['last_run_id' => $arrResponse['id'] ?? ''])
            ->limit(1)
            ->execute($strThreadId);

        return $arrResponse;
    }

    public function getMessages(): array
    {

        $arrCurrentRun = $this->retrieveRun();

        if (($arrCurrentRun['status'] ?? '') !== 'completed') {
            return [];
        }

        $strThreadId = $this->getThreadId();
        $objCurl = \curl_init(sprintf(Statics::URL_CREATE_THREAD_MESSAGE, $strThreadId));

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $this->strToken",
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        \curl_close($objCurl);

        return $arrResponse;
    }
}
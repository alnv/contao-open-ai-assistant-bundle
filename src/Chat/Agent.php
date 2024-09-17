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

    private string $strToken;

    protected array $arrOptions; // thread_id, description, instructions, files

    protected string $strName;

    protected Assistant $objAssistant;

    protected VectorStore $objVectorStore;

    protected string $strThreadId = '';

    protected array $arrUploadFiles = [];

    public function __construct(string $strName, array $arrOptions = [])
    {

        $this->strToken = Config::get('openaiApi') ?: '';

        $this->strName = $strName;
        $this->arrOptions = $arrOptions;

        $this->objAssistant = $this->createAssistant();
        $this->uploadFile();
        $this->objVectorStore = $this->createVectorStore();

        $strState = $this->objVectorStore->getVectorStore()['state'] ?? '';
        if ($strState && $strState !== 'completed') {
            $this->objVectorStore->retrieve();
        }

        if ($strState === 'completed') {
            $this->objAssistant->addVectorStore($this->strName);
        }

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

        $objChatBot = $this->getCurrentChatBotArray();

        if (empty($objChatBot)) {

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

    protected function getCurrentChatBotArray(): array
    {
        $strThreadId = $this->getThreadId();

        return Database::getInstance()->prepare('SELECT * FROM tl_ai_chat_threads WHERE `thread_id`=?')->limit(1)->execute($strThreadId)->row();
    }

    public function addMessage($strPrompt): Agent
    {

        $strThreadId = $this->getThreadId();
        $arrChatBot = $this->getCurrentChatBotArray();

        $strPrompt = StringUtil::decodeEntities($strPrompt);
        $strLastPrompt = StringUtil::decodeEntities($arrChatBot['last_prompt']);

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
            ->set([
                'last_prompt' => $strPrompt
            ])
            ->limit(1)
            ->execute($strThreadId);

        return $this;
    }

    public function retrieveRun(): array
    {

        $arrChatBot = $this->getCurrentChatBotArray();

        $strThreadId = $this->getThreadId();
        $strRunId = $arrChatBot['last_run_id'] ?? '';

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

    public function run()
    {

        $strThreadId = $this->getThreadId();

        $arrData = [
            'assistant_id' => $this->objAssistant->getAssistantId()
        ];

        $objCurl = \curl_init(sprintf(Statics::URL_RUN_THREAD, $strThreadId));

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
            ->set([
                'last_run_id' => $arrResponse['id'] ?? ''
            ])
            ->limit(1)
            ->execute($strThreadId);

        return $arrResponse;
    }

    public function getMessages($intTries = 0): array
    {

        if ($intTries === 10) {
            return [];
        }

        $intTries = $intTries + 1;
        $arrCurrentRun = $this->retrieveRun();

        if (empty($arrCurrentRun)) {
            $this->run();
            return $this->getMessages($intTries);
        }

        if ($arrCurrentRun['status'] !== 'completed') {
            sleep(1);
            return $this->getMessages($intTries);
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

    protected function createAssistant(): Assistant
    {

        $objAssistant = new Assistant($this->strName);

        if (!$objAssistant->exist()) {
            $objAssistant->create([
                'description' => $this->arrOptions['description'] ?? '',
                'instructions' => $this->arrOptions['instructions'] ?? ''
            ]);
        }

        return $objAssistant;
    }

    protected function uploadFile(): void
    {

        $arrFiles = $this->arrOptions['files'] ?? [];

        foreach ($arrFiles as $strFile) {
            $objFileUpload = new FileUpload($this->strName . '__' . md5($strFile));
            $objFileUpload->create($strFile);

            $this->arrUploadFiles[] = $objFileUpload;
        }
    }

    protected function createVectorStore(): VectorStore
    {

        $arrFiles = [];
        foreach ($this->arrUploadFiles as $objUploadFile) {
            if ($strFileName = ($objUploadFile->getFileUpload()['name'] ?? '')) {
                $arrFiles[] = $strFileName;
            }
        }

        $objVectorStore = new VectorStore($this->strName);

        if (empty($arrFiles)) {
            return $objVectorStore;
        }

        $objVectorStore->create([
            'file_names' => $arrFiles
        ]);

        return $objVectorStore;
    }
}
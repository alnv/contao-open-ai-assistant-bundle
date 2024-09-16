<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Library;

use Contao\Database;
use Contao\StringUtil;
use Alnv\ContaoOpenAiAssistantBundle\Helpers\Statics;

class Assistant extends ChatGPT
{

    protected string $strName;

    protected array $arrAssistant = [];

    public function __construct($strName)
    {
        parent::__construct();

        $this->strName = $strName;
        $this->setAssistant();
    }

    public function create($arrOptions): void
    {

        $objEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_assistants WHERE `name`=?')->limit(1)->execute($this->strName);

        if (!$objEntity->numRows) {

            $arrSet = [
                'tstamp' => time(),
                'name' => $this->strName,
                'description' => $arrOptions['description'] ?? '',
                'instructions' => $arrOptions['instructions'] ?? ''
            ];

            Database::getInstance()->prepare('INSERT INTO tl_ai_assistants %s')->set($arrSet)->execute();
        }

        $this->setAssistant();
        $this->createAssistantId();
    }

    public function exist(): bool
    {
        $objEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_assistants WHERE `name`=?')->limit(1)->execute($this->strName);

        return (bool)$objEntity->numRows;
    }

    protected function setAssistant(): void
    {

        $objAssistant = Database::getInstance()->prepare('SELECT * FROM tl_ai_assistants WHERE `name`=?')->limit(1)->execute($this->strName);

        foreach ($objAssistant->row() as $strField => $strValue) {

            switch ($strField) {
                case 'id':
                case 'assistant_id':
                case 'description':
                case 'instructions':
                    $this->arrAssistant[$strField] = $strValue;
                    break;
                case 'vector_stores':
                    $this->arrAssistant[$strField] = StringUtil::deserialize($strValue, true);
                    break;
            }
        }
    }

    public function deleteAssistantId(): void
    {
        $arrAssistant = $this->getAssistant();
        $strAssistantId = $arrAssistant['assistant_id'] ?? '';

        $objCurl = \curl_init(sprintf(Statics::URL_DELETE_ASSISTANT, $strAssistantId));

        \curl_setopt($objCurl, CURLOPT_CUSTOMREQUEST, "DELETE");
        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getToken(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        Database::getInstance()
            ->prepare('UPDATE tl_ai_assistants %s WHERE id=?')
            ->set(['assistant_id' => ''])
            ->limit(1)
            ->execute($arrAssistant['id']);

        $this->arrAssistant['assistant_id'] = '';
    }

    public function getAssistantId(): string
    {
        return $this->arrAssistant['assistant_id'] ?? '';
    }

    public function modifyAssistantId($arrAssistantData): void
    {

        $arrAssistant = $this->getAssistant();
        $strAssistantId = $arrAssistant['assistant_id'] ?? '';

        if (!$strAssistantId) {
            return;
        }

        $objCurl = \curl_init(sprintf(Statics::URL_MODIFY_ASSISTANT, $strAssistantId));

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getToken(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        \curl_setopt($objCurl, CURLOPT_POST, true);
        \curl_setopt($objCurl, CURLOPT_POSTFIELDS, json_encode($arrAssistantData));

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        $arrSet = [
            'tstamp' => time()
        ];

        if ($strName = $arrAssistantData['name']) {
            $arrSet['name'] = $strName;
        }
        if ($strDescription = $arrAssistantData['description']) {
            $arrSet['description'] = $strDescription;
        }
        if ($strInstructions = $arrAssistantData['instructions']) {
            $arrSet['instructions'] = $strInstructions;
        }

        Database::getInstance()
            ->prepare('UPDATE tl_ai_assistants %s WHERE id=?')
            ->set($arrSet)
            ->limit(1)
            ->execute($arrAssistant['id']);
    }

    public function createAssistantId(): string
    {

        $arrAssistant = $this->getAssistant();
        $strAssistantId = $arrAssistant['assistant_id'] ?? '';

        if ($strAssistantId) {
            return $strAssistantId;
        }

        $arrData = [
            'name' => $this->strName,
            'description' => $arrAssistant['description'] ?? '',
            'instructions' => $arrAssistant['instructions'] ?? '',
            'model' => Statics::CHAT_GPT_MODEL,
            'tools' => [
                ['type' => 'file_search']
            ]
        ];

        $objCurl = \curl_init(Statics::URL_CREATE_ASSISTANT);

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getToken(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        \curl_setopt($objCurl, CURLOPT_POST, true);
        \curl_setopt($objCurl, CURLOPT_POSTFIELDS, json_encode($arrData));

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        $strAssistantId = ($arrResponse['id'] ?? '');

        Database::getInstance()
            ->prepare('UPDATE tl_ai_assistants %s WHERE id=?')
            ->set(['assistant_id' => $strAssistantId])
            ->limit(1)
            ->execute($arrAssistant['id']);

        $this->arrAssistant['assistant_id'] = $strAssistantId;

        return $strAssistantId;
    }

    public function getAssistant(): array
    {
        return $this->arrAssistant;
    }

    public function addVectorStore($strVectorName): bool
    {

        $arrAssistant = $this->getAssistant();
        $objVectorStore = new VectorStore($strVectorName);
        $strVectorStoreId = $objVectorStore->getVectorStore()['vector_store_id'] ?? '';
        $strAssistantId = $arrAssistant['assistant_id'] ?? '';

        if (!$strVectorStoreId || !$strAssistantId) {
            return false;
        }

        if (in_array($strVectorStoreId, $arrAssistant['vector_stores'])) {
            return true;
        }

        $this->arrAssistant['vector_stores'][] = $strVectorStoreId;

        $arrData = [
            'tool_resources' => [
                'file_search' => [
                    'vector_store_ids' => $this->arrAssistant['vector_stores']
                ]
            ]
        ];

        $this->modifyAssistantId($arrData);

        Database::getInstance()
            ->prepare('UPDATE tl_ai_assistants %s WHERE id=?')
            ->set([
                'vector_stores' => \serialize($this->arrAssistant['vector_stores'])
            ])
            ->limit(1)
            ->execute($arrAssistant['id']);

        return true;
    }
}
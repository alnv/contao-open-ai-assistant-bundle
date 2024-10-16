<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Library;

use Contao\Database;
use Contao\StringUtil;
use Alnv\ContaoOpenAiAssistantBundle\Helpers\Statics;

class VectorStore extends ChatGPT
{

    protected string $strName;

    protected array $arrVectorStore = [];

    public function __construct($strName)
    {
        parent::__construct();

        $this->strName = $strName;
        $this->setVectorStore();
    }

    public function create($arrOptions = []): void
    {

        $arrFilesId = $arrOptions['file_ids'] ?? [];
        $objEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores WHERE `name`=?')->limit(1)->execute($this->strName);

        if (!$objEntity->numRows) {

            if (!empty($arrOptions['file_names'])) {
                foreach ($arrOptions['file_names'] as $strFileNames) {
                    $objFileUpload = new FileUpload($strFileNames);
                    if ($strFileId = $objFileUpload->getFileId()) {
                        $arrFilesId[] = $strFileId;
                    }
                }
            }

            $arrSet = [
                'tstamp' => \time(),
                'name' => $this->strName,
                'file_ids' => \serialize($arrFilesId)
            ];

            Database::getInstance()->prepare('INSERT INTO tl_ai_vector_stores %s')->set($arrSet)->execute();
        }

        $this->setVectorStore();
        $this->createVectorStoreId();
    }

    public function deleteVectorStoreId(): void
    {

        $arrVectorStore = $this->getVectorStore();
        $strVectorStoreId = $arrVectorStore['vector_store_id'] ?? '';

        if (!$strVectorStoreId) {
            return;
        }

        $objCurl = \curl_init(sprintf(Statics::URL_DELETE_VECTOR_STORES, $strVectorStoreId));

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
            ->prepare('UPDATE tl_ai_vector_stores %s WHERE id=?')
            ->set(['vector_store_id' => ''])
            ->limit(1)
            ->execute($arrVectorStore['id']);

        $this->arrVectorStore['vector_store_id'] = '';
    }

    protected function setVectorStore(): void
    {

        $objAssistant = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores WHERE `name`=?')->limit(1)->execute($this->strName);

        foreach ($objAssistant->row() as $strField => $strValue) {

            switch ($strField) {
                case 'vector_store_id':
                case 'id':
                case 'state':
                    $this->arrVectorStore[$strField] = $strValue;
                    break;
                case 'file_ids':
                    $this->arrVectorStore[$strField] = StringUtil::deserialize($strValue, true);
                    break;
            }
        }
    }

    public function getVectorStore(): array
    {
        return $this->arrVectorStore;
    }

    public function setFileIds($arrFileIds): void
    {
        $this->arrVectorStore['file_ids'] = $arrFileIds;
    }

    public function retrieve(): array
    {

        $arrVectorStore = $this->getVectorStore();
        $strVectorStoreId = $arrVectorStore['vector_store_id'] ?? '';

        if (!$strVectorStoreId) {
            return [];
        }

        $objCurl = \curl_init(sprintf(Statics::URL_RETRIEVE_VECTOR_STORES, $strVectorStoreId));

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getToken(),
            "Content-Type: application/json",
            "OpenAI-Beta: assistants=v2"
        ]);

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        Database::getInstance()
            ->prepare('UPDATE tl_ai_vector_stores %s WHERE id=?')
            ->set(['state' => $arrResponse['status'] ?? '', 'tstamp' => time()])
            ->limit(1)
            ->execute($arrVectorStore['id']);

        $this->setVectorStore();

        return $arrResponse;
    }

    public function createVectorStoreId(): string
    {

        $arrVectorStore = $this->getVectorStore();
        $strVectorStoreId = $arrVectorStore['vector_store_id'] ?? '';

        if ($strVectorStoreId) {
            return $strVectorStoreId;
        }

        if (empty($arrVectorStore['file_ids'])) {
            return $strVectorStoreId;
        }

        $arrData = [
            'name' => $this->strName,
            'file_ids' => $arrVectorStore['file_ids']
        ];

        $objCurl = \curl_init(Statics::URL_CREATE_VECTOR_STORES);

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

        $strVectorStoreId = ($arrResponse['id'] ?? '');

        Database::getInstance()
            ->prepare('UPDATE tl_ai_vector_stores %s WHERE id=?')
            ->set([
                'file_ids' => \serialize($arrVectorStore['file_ids']),
                'vector_store_id' => $strVectorStoreId,
                'state' => $arrResponse['status'] ?? ''
            ])
            ->limit(1)
            ->execute($arrVectorStore['id']);

        $this->arrVectorStore['vector_store_id'] = $strVectorStoreId;

        return $strVectorStoreId;
    }
}
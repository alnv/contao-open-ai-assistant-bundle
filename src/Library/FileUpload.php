<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Library;

use Contao\StringUtil;
use Contao\System;
use Contao\Database;
use Contao\FilesModel;
use Alnv\ContaoOpenAiAssistantBundle\Helpers\Statics;

class FileUpload extends ChatGPT
{

    protected string $strName;

    protected array $arrFileUpload = [];

    public function __construct($strName)
    {
        parent::__construct();

        $this->strName = $strName;
        $this->setFileUpload();
    }

    public function create($strFile, $arrOptions = []): void
    {

        $objFile = FilesModel::findByPath($strFile);

        if (!$objFile) {
            throw new \RuntimeException($strFile . ' not found');
        }

        $objEntity = Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads WHERE `name`=?')->limit(1)->execute($this->strName);
        if (!$objEntity->numRows) {

            $arrSet = [
                'tstamp' => time(),
                'name' => $this->strName,
                'purpose' => $arrOptions['purpose'] ?? 'assistants',
                'file' => StringUtil::binToUuid($objFile->uuid)
            ];

            Database::getInstance()->prepare('INSERT INTO tl_ai_file_uploads %s')->set($arrSet)->execute();
        }

        $this->setFileUpload();
        $this->uploadFile();
    }

    protected function setFileUpload(): void
    {

        $objFileUpload = Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads WHERE `name`=?')->limit(1)->execute($this->strName);

        foreach ($objFileUpload->row() as $strField => $strValue) {

            switch ($strField) {
                case 'id':
                case 'name':
                case 'purpose':
                case 'file_id':
                    $this->arrFileUpload[$strField] = $strValue;
                    break;
            }

            if ($strField === 'file' && ($objFile = FilesModel::findByUuid($strValue))) {
                $this->arrFileUpload[$strField] = $objFile->path;
            }
        }
    }

    public function getFileUpload(): array
    {
        return $this->arrFileUpload;
    }

    public function getFileId(): string
    {
        return $this->arrFileUpload['file_id'] ?? '';
    }

    public function deleteFileId(): void
    {
        $arrFileUpload = $this->getFileUpload();
        $strFileId = $arrFileUpload['file_id'] ?? '';

        $objCurl = \curl_init(sprintf(Statics::URL_DELETE_FILE_UPLOAD, $strFileId));

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
            ->prepare('UPDATE tl_ai_file_uploads %s WHERE id=?')
            ->set(['file_id' => ''])
            ->limit(1)
            ->execute($arrFileUpload['id']);

        $this->arrFileUpload['file_id'] = '';
    }

    public function uploadFile(): string
    {

        $arrFileUpload= $this->getFileUpload();
        $strFileId = $arrFileUpload['file_id'] ?? '';
        $strRootDir = System::getContainer()->getParameter('kernel.project_dir');

        if ($strFileId) {
            return $strFileId;
        }

        if (!($arrFileUpload['file'] ?? '')) {
            return '';
        }

        $objCurl = \curl_init(Statics::URL_CREATE_FILE_UPLOAD);

        \curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($objCurl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $this->getToken(),
            'Content-Type: multipart/form-data'
        ]);

        \curl_setopt($objCurl, CURLOPT_POST, true);
        \curl_setopt($objCurl, CURLOPT_POSTFIELDS, [
            'purpose' => $arrFileUpload['purpose'] ?? 'assistants',
            'file' => new \CURLFile($strRootDir . '/' . $arrFileUpload['file'])
        ]);

        $objResponse = \curl_exec($objCurl);
        $arrResponse = \json_decode($objResponse, true);

        if (!empty($arrResponse) && isset($arrResponse['error'])) {
            throw new \RuntimeException($arrResponse['error']['message'] ?? '');
        }

        $strFileId = ($arrResponse['id'] ?? '');

        Database::getInstance()
            ->prepare('UPDATE tl_ai_file_uploads %s WHERE id=?')
            ->set(['file_id' => $strFileId])
            ->limit(1)
            ->execute($arrFileUpload['id']);

        $this->arrFileUpload['file_id'] = $strFileId;

        return $strFileId;
    }
}
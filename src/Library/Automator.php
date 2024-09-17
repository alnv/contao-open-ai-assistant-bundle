<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Library;

use Alnv\ContaoOpenAiAssistantBundle\Helpers\Getters;
use Contao\FilesModel;

class Automator
{

    public static function updateVectorStoresByFilePath($strFilePath, $strName = ''): void
    {

        $objFile = FilesModel::findByPath($strFilePath);
        if (!$objFile) {
            return;
        }

        $arrFileUpload = Getters::getFileUploadByFile($objFile->uuid);
        $strFileUploadName = $arrFileUpload['name'] ?: $strName;

        if (!$strFileUploadName) {
            return;
        }

        $objFileUpload = new FileUpload($strFileUploadName);
        $strOldFileId = $objFileUpload->getFileId();

        if (!$strOldFileId) {
            $objFileUpload->create($strFilePath);
        } else {
            $objFileUpload->deleteFileId();
        }

        $strNewFileId = $objFileUpload->uploadFile();


        $arrVectorStores = Getters::getVectorStoresByFileUploadId($strOldFileId);

        if (empty($arrVectorStores) && $strNewFileId) {

            $objVectorStore = new VectorStore($strFileUploadName);
            $objVectorStore->create([
                'file_ids' => [$strNewFileId]
            ]);
        }

        if (!empty($arrVectorStores)) {

            foreach ($arrVectorStores as $arrVectorStore) {

                $objVectorStore = new VectorStore($arrVectorStore['name']);

                $strOldVectorStoreId = $objVectorStore->getVectorStore()['vector_store_id'];
                $arrFileIds = $strNewFileId ? [$strNewFileId] : [];

                foreach ($objVectorStore->getVectorStore()['file_ids'] as $strFileId) {
                    if ($strOldFileId != $strFileId || !$strOldFileId) {
                        $arrFileIds[] = $strFileId;
                    }
                }

                $objVectorStore->deleteVectorStoreId();
                $objVectorStore->setFileIds($arrFileIds);
                $strNewVectorStoreId = $objVectorStore->createVectorStoreId();

                if ($strNewVectorStoreId && $strOldVectorStoreId) {

                    $arrAssistants = Getters::getAssistantsByVectorStoreId($strOldVectorStoreId);
                    foreach ($arrAssistants as $arrAssistant) {

                        $objAssistant = new Assistant($arrAssistant['name']);
                        $arrNewVectorStores = [$strNewVectorStoreId];

                        foreach ($objAssistant->getAssistant()['vector_stores'] as $strVectorStoreId) {
                            if ($strVectorStoreId != $strOldVectorStoreId) {
                                $arrNewVectorStores[] = $strVectorStoreId;
                            }
                        }

                        $objAssistant->updateVectorStore($arrNewVectorStores);
                    }
                }
            }
        }
    }
}
<?php

namespace Alnv\ContaoOpenAiAssistantBundle\Helpers;

use Contao\Database;
use Contao\StringUtil;

class Getters
{

    public static function getFileUploadByFile($strFileUuid): array
    {
        return Database::getInstance()->prepare('SELECT * FROM tl_ai_file_uploads WHERE file=?')->limit(1)->execute($strFileUuid)->row();
    }

    public static function getVectorStoresByFileUploadId($strFileUploadId): array
    {

        $arrVectorStores = [];
        $objVectorStoreEntities = Database::getInstance()->prepare('SELECT * FROM tl_ai_vector_stores ORDER BY `name`')->execute();

        while ($objVectorStoreEntities->next()) {

            $arrFileUploadIds = StringUtil::deserialize($objVectorStoreEntities->file_ids, true);
            if (in_array($strFileUploadId, $arrFileUploadIds)) {
                $arrVectorStores[] = $objVectorStoreEntities->row();
            }
        }

        return $arrVectorStores;
    }

    public static function getAssistantsByVectorStoreId($strVectorStoreId): array
    {

        $arrAssistants = [];
        $objAssistantsEntities = Database::getInstance()->prepare('SELECT * FROM tl_ai_assistants ORDER BY `name`')->execute();

        while ($objAssistantsEntities->next()) {

            $arrVectorStores = StringUtil::deserialize($objAssistantsEntities->vector_stores, true);
            if (in_array($strVectorStoreId, $arrVectorStores)) {
                $arrAssistants[] = $objAssistantsEntities->row();
            }
        }

        return $arrAssistants;
    }
}
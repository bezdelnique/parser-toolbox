<?php
/**
 * Created by PhpStorm.
 * User: heman
 * Date: 04.12.2018
 * Time: 15:00
 */

namespace bezdelnique\parserToolbox;


class ParserTools
{
    static public function getPageContentCodeByUrl(string $url): array
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        // curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        $content = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return [$content, $httpCode];
    }


    static public function getBinaryContentCodeByUrl(string $url): array
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        // curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($handle, CURLOPT_HEADER, 0);
        $binaryFile = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return [$binaryFile, $httpCode];
    }

    static public function getFileNameByUrl(string $url): string
    {
        /*
        $fileExt = '';
        switch ($fileType) {
            case self::FILE_TYPE_HTML:
                $fileExt = 'html';
                break;

        }

        if (empty($fileExt)) {
            throw new ExceptionComponent(sprintf('Не удалось задать ext для типа %s', $fileType));
        }
        */

        $fileName = rtrim($url, '/');
        $fileName = str_replace(['/', ':', '&'], '-', $fileName);
        $fileName = str_replace('?', '=', $fileName);

        return $fileName;
    }


    static public function saveFileBinary($fileName, $binaryContent): array
    {
        if (!$handle = fopen($fileName, 'w')) {
            throw new ToolboxException(sprintf('Не могу открыть файл %s.', $fileName));
        }

        if (fwrite($handle, $binaryContent) === FALSE) {
            throw new ToolboxException(sprintf('Не могу произвести запись в файл %s.', $fileName));
        }

        fclose($handle);

        $fileBodyMd5 = md5_file($fileName);
        $fileSize = filesize($fileName);

        return [$fileBodyMd5, $fileSize];
    }


    static public function getPath(string $companyNick)
    {
        $path = \Yii::getAlias(sprintf('@parser/download/%s', $companyNick));
        return $path;
    }
}


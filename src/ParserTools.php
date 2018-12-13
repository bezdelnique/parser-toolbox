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
        $fileName = rtrim($url, '/');
        $fileName = str_replace(['/', ':', '&'], '-', $fileName);
        $fileName = str_replace('?', '=', $fileName);

        return $fileName;
    }


    static public function saveFileBinary($fileName, $binaryContent): array
    {
        if (!$handle = fopen($fileName, 'w')) {
            throw new ToolboxException(sprintf('Unable to open file: %s.', $fileName));
        }

        if (fwrite($handle, $binaryContent) === FALSE) {
            throw new ToolboxException(sprintf('Unable write to file: %s.', $fileName));
        }

        fclose($handle);

        $fileBodyMd5 = md5_file($fileName);
        $fileSize = filesize($fileName);

        return [$fileBodyMd5, $fileSize];
    }
}


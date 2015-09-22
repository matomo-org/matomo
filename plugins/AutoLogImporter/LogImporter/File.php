<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\AutoLogImporter\LogImporter;

class File
{
    public function getNumberOfLines($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            return 0;
        }

        $numLines   = 0;
        $fileHandle = fopen($file, 'rb');

        while (!feof($fileHandle)) {
            $numLines += substr_count(fread($fileHandle, 8192), "\n");
        }

        // +1 for fist line
        $numLines++;

        fclose($fileHandle);

        return $numLines;
    }

    public function getSize($file)
    {
        return filesize($file);
    }

    public function getHash($file)
    {
        return md5_file($file);
    }

    public function getPathToVerifyHashFile($file)
    {
        return $file . '.hash';
    }

    public function getVerifyHash($file)
    {
        if (!$this->hasVerifyHash($file)) {
            return;
        }

        $hash = file_get_contents($this->getPathToVerifyHashFile($file));

        if (!empty($hash)) {
            return trim($hash);
        }
    }

    public function hasVerifyHash($file)
    {
        return file_exists($this->getPathToVerifyHashFile($file));
    }

    public function wasFileSuccessfullyCopiedViaFileSynchronizerPlugin($logFile, $hash)
    {
        $verifyHash = $this->getVerifyHash($logFile);

        return !empty($verifyHash) && $verifyHash === trim($hash);
    }
}

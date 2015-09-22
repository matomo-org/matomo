<?php
/**
 * Copyright (C) Piwik PRO - All rights reserved.
 *
 * Using this code requires that you first get a license from Piwik PRO.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * @link http://piwik.pro
 */

namespace Piwik\Plugins\FileSynchronizer\SyncFiles;

class File
{
    public function getSize($file)
    {
        return filesize($file);
    }

    public function getHash($file)
    {
        return md5_file($file);
    }

    public function buildTargetFileName($sourceFile, $targetFilenameTemplate)
    {
        $basename  = basename($sourceFile);
        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
        $filename  = pathinfo($sourceFile, PATHINFO_FILENAME);

        $search  = array('$basename', '$filename', '$extension');
        $replace = array($basename, $filename, $extension);

        $targetBasename = str_replace($search, $replace, $targetFilenameTemplate);

        return $targetBasename;
    }

    public function buildTargetFilePath($sourceFile, $targetDirectory, $targetFilenameTemplate)
    {
        $targetBasename = $this->buildTargetFileName($sourceFile, $targetFilenameTemplate);

        return $targetDirectory . DIRECTORY_SEPARATOR . $targetBasename;
    }
}

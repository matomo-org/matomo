<?php
/**
* Matomo - free/libre analytics platform
*
* @link https://matomo.org
* @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
*/
namespace Piwik;

use Piwik\Tests\Framework\Mock\File;

function filesize($filename)
{
    if (File::getFileSize() !== null) {
        return File::getFileSize();
    }

    return \filesize($filename);
}

function file_exists($filename)
{
    if (File::getFileExists() !== null) {
        return File::getFileExists();
    }

    return \file_exists($filename);
}

namespace Piwik\Tests\Framework\Mock;

class File
{
    private static $filesize = null;
    private static $fileExists = null;

    public static function getFileSize()
    {
        return self::$filesize;
    }

    public static function setFileSize($filesize)
    {
        self::$filesize = $filesize;
    }

    public static function reset()
    {
        self::$filesize = null;
        self::$fileExists = null;
    }

    public static function getFileExists()
    {
        return self::$fileExists;
    }

    public static function setFileExists($exists)
    {
        self::$fileExists = $exists;
    }
}

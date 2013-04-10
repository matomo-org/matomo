<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Unzip wrapper around ZipArchive and PclZip
 *
 * @package Piwik
 */
class Piwik_Unzip
{
    /**
     * Factory method to create an unarchiver
     *
     * @param string $name      Name of unarchiver
     * @param string $filename  Name of .zip archive
     * @return Piwik_Unzip_Interface
     */
    static public function factory($name, $filename)
    {
        switch ($name) {
            case 'ZipArchive':
                if (class_exists('ZipArchive', false))
                    return new Piwik_Unzip_ZipArchive($filename);
                break;
            case 'tar.gz':
                return new Piwik_Unzip_Tar($filename, 'gz');
            case 'tar.bz2':
                return new Piwik_Unzip_Tar($filename, 'bz2');
            case 'gz':
                if (function_exists('gzopen'))
                    return new Piwik_Unzip_Gzip($filename);
                break;
            case 'PclZip':
            default:
                return new Piwik_Unzip_PclZip($filename);
        }

        return new Piwik_Unzip_PclZip($filename);
    }
}

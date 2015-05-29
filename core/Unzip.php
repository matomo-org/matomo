<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\Decompress\Gzip;
use Piwik\Decompress\PclZip;
use Piwik\Decompress\Tar;
use Piwik\Decompress\ZipArchive;

/**
 * Factory for Decompress adapters.
 */
class Unzip
{
    /**
     * Factory method to create an unarchiver
     *
     * @param string $name Name of unarchiver
     * @param string $filename Name of .zip archive
     * @return \Piwik\Decompress\DecompressInterface
     */
    public static function factory($name, $filename)
    {
        switch ($name) {
            case 'ZipArchive':
                if (class_exists('ZipArchive', false)) {
                    return new ZipArchive($filename);
                }
                break;
            case 'tar.gz':
                return new Tar($filename, 'gz');
            case 'tar.bz2':
                return new Tar($filename, 'bz2');
            case 'gz':
                if (function_exists('gzopen')) {
                    return new Gzip($filename);
                }
                break;
            case 'PclZip':
            default:
                return new PclZip($filename);
        }

        return new PclZip($filename);
    }
}

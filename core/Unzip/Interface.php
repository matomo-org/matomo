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
 * Unzip interface
 *
 * @package Piwik
 * @subpackage Piwik_Unzip
 */
interface Piwik_Unzip_Interface
{
    /**
     * Constructor
     *
     * @param string $filename  Name of the .zip archive
     */
    public function __construct($filename);

    /**
     * Extract files from archive to target directory
     *
     * @param string $pathExtracted  Absolute path of target directory
     * @return mixed  Array of filenames if successful; or 0 if an error occurred
     */
    public function extract($pathExtracted);

    /**
     * Get error status string for the latest error
     *
     * @return string
     */
    public function errorInfo();
}

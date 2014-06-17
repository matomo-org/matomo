<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Unzip;

use Archive_Tar;

/**
 * @see libs/Archive_Tar
 */
require_once PIWIK_INCLUDE_PATH . '/libs/Archive_Tar/Tar.php';

/**
 * Unzip implementation for Archive_Tar PEAR lib.
 *
 */
class Tar implements UncompressInterface
{
    /**
     * Archive_Tar instance.
     *
     * @var Archive_Tar
     */
    private $tarArchive = null;

    /**
     * Constructor.
     *
     * @param string $filename Path to tar file.
     * @param string|null $compression Either 'gz', 'bz2' or null for no compression.
     */
    public function __construct($filename, $compression = null)
    {
        $this->tarArchive = new Archive_Tar($filename, $compression);
    }

    /**
     * Extracts the contents of the tar file to $pathExtracted.
     *
     * @param string $pathExtracted Directory to extract into.
     * @return bool true if successful, false if otherwise.
     */
    public function extract($pathExtracted)
    {
        return $this->tarArchive->extract($pathExtracted);
    }

    /**
     * Extracts one file held in a tar archive and returns the deflated file
     * as a string.
     *
     * @param string $inArchivePath Path to file in the tar archive.
     * @return bool true if successful, false if otherwise.
     */
    public function extractInString($inArchivePath)
    {
        return $this->tarArchive->extractInString($inArchivePath);
    }

    /**
     * Lists the files held in the tar archive.
     *
     * @return array List of paths describing everything held in the tar archive.
     */
    public function listContent()
    {
        return $this->tarArchive->listContent();
    }

    /**
     * Get error status string for the latest error.
     *
     * @return string
     */
    public function errorInfo()
    {
        return $this->tarArchive->error_object->getMessage();
    }
}

<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Unzip;

/**
 * @see libs/PclZip
 */
require_once PIWIK_INCLUDE_PATH . '/libs/PclZip/pclzip.lib.php';

/**
 * Unzip wrapper around PclZip
 *
 */
class PclZip implements UncompressInterface
{
    /**
     * @var PclZip
     */
    private $pclzip;
    /**
     * @var string
     */
    public $filename;

    /**
     * Constructor
     *
     * @param string $filename Name of the .zip archive
     */
    public function __construct($filename)
    {
        $this->pclzip = new \PclZip($filename);
        $this->filename = $filename;
    }

    /**
     * Extract files from archive to target directory
     *
     * @param string $pathExtracted Absolute path of target directory
     * @return mixed  Array of filenames if successful; or 0 if an error occurred
     */
    public function extract($pathExtracted)
    {
        $pathExtracted = str_replace('\\', '/', $pathExtracted);
        $list = $this->pclzip->listContent();
        if (empty($list)) {
            return 0;
        }

        foreach ($list as $entry) {
            $filename = str_replace('\\', '/', $entry['stored_filename']);
            $parts = explode('/', $filename);

            if (!strncmp($filename, '/', 1) ||
                array_search('..', $parts) !== false ||
                strpos($filename, ':') !== false
            ) {
                return 0;
            }
        }

        // PCLZIP_CB_PRE_EXTRACT callback returns 0 to skip, 1 to resume, or 2 to abort
        return $this->pclzip->extract(
            PCLZIP_OPT_PATH, $pathExtracted,
            PCLZIP_OPT_STOP_ON_ERROR,
            PCLZIP_OPT_REPLACE_NEWER,
            PCLZIP_CB_PRE_EXTRACT, function ($p_event, &$p_header) use ($pathExtracted) {
                return strncmp($p_header['filename'], $pathExtracted, strlen($pathExtracted)) ? 0 : 1;
            }
        );
    }

    /**
     * Get error status string for the latest error
     *
     * @return string
     */
    public function errorInfo()
    {
        return $this->pclzip->errorInfo(true);
    }
}

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
 * Unzip implementation for .gz files.
 *
 */
class Gzip implements UncompressInterface
{
    /**
     * Name of .gz file.
     *
     * @var string
     */
    private $filename = null;

    /**
     * Error string.
     *
     * @var string
     */
    private $error = null;

    /**
     * Constructor.
     *
     * @param string $filename Name of .gz file.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Extracts the contents of the .gz file to $pathExtracted.
     *
     * @param string $pathExtracted Must be file, not directory.
     * @return bool true if successful, false if otherwise.
     */
    public function extract($pathExtracted)
    {
        $file = gzopen($this->filename, 'r');
        if ($file === false) {
            $this->error = "gzopen failed";
            return false;
        }

        $output = fopen($pathExtracted, 'w');
        while (!feof($file)) {
            fwrite($output, fread($file, 1024 * 1024));
        }
        fclose($output);

        $success = gzclose($file);
        if ($success === false) {
            $this->error = "gzclose failed";
            return false;
        }

        return true;
    }

    /**
     * Get error status string for the latest error.
     *
     * @return string
     */
    public function errorInfo()
    {
        return $this->error;
    }
}


<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Unzip;

use Exception;

/**
 * Unzip wrapper around ZipArchive
 *
 */
class ZipArchive implements UncompressInterface
{
    /**
     * @var \ZipArchive
     */
    private $ziparchive;
    /**
     * @var string
     */
    public $filename;

    /**
     * Constructor
     *
     * @param string $filename Name of the .zip archive
     * @throws Exception
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->ziparchive = new \ZipArchive;
        if ($this->ziparchive->open($filename) !== true) {
            throw new Exception('Error opening ' . $filename);
        }
    }

    /**
     * Extract files from archive to target directory
     *
     * @param string $pathExtracted Absolute path of target directory
     * @return mixed  Array of filenames if successful; or 0 if an error occurred
     */
    public function extract($pathExtracted)
    {
        if (substr($pathExtracted, -1) !== '/') {
            $pathExtracted .= '/';
        }

        $fileselector = array();
        $list = array();
        $count = $this->ziparchive->numFiles;
        if ($count === 0) {
            return 0;
        }

        for ($i = 0; $i < $count; $i++) {
            $entry = $this->ziparchive->statIndex($i);

            $filename = str_replace('\\', '/', $entry['name']);
            $parts = explode('/', $filename);

            if (!strncmp($filename, '/', 1) ||
                array_search('..', $parts) !== false ||
                strpos($filename, ':') !== false
            ) {
                return 0;
            }
            $fileselector[] = $entry['name'];
            $list[] = array(
                'filename'        => $pathExtracted . $entry['name'],
                'stored_filename' => $entry['name'],
                'size'            => $entry['size'],
                'compressed_size' => $entry['comp_size'],
                'mtime'           => $entry['mtime'],
                'index'           => $i,
                'crc'             => $entry['crc'],
            );
        }

        $res = $this->ziparchive->extractTo($pathExtracted, $fileselector);
        if ($res === false)
            return 0;
        return $list;
    }

    /**
     * Get error status string for the latest error
     *
     * @return string
     */
    public function errorInfo()
    {
        static $statusStrings = array(
            \ZIPARCHIVE::ER_OK          => 'No error',
            \ZIPARCHIVE::ER_MULTIDISK   => 'Multi-disk zip archives not supported',
            \ZIPARCHIVE::ER_RENAME      => 'Renaming temporary file failed',
            \ZIPARCHIVE::ER_CLOSE       => 'Closing zip archive failed',
            \ZIPARCHIVE::ER_SEEK        => 'Seek error',
            \ZIPARCHIVE::ER_READ        => 'Read error',
            \ZIPARCHIVE::ER_WRITE       => 'Write error',
            \ZIPARCHIVE::ER_CRC         => 'CRC error',
            \ZIPARCHIVE::ER_ZIPCLOSED   => 'Containing zip archive was closed',
            \ZIPARCHIVE::ER_NOENT       => 'No such file',
            \ZIPARCHIVE::ER_EXISTS      => 'File already exists',
            \ZIPARCHIVE::ER_OPEN        => 'Can\'t open file',
            \ZIPARCHIVE::ER_TMPOPEN     => 'Failure to create temporary file',
            \ZIPARCHIVE::ER_ZLIB        => 'Zlib error',
            \ZIPARCHIVE::ER_MEMORY      => 'Malloc failure',
            \ZIPARCHIVE::ER_CHANGED     => 'Entry has been changed',
            \ZIPARCHIVE::ER_COMPNOTSUPP => 'Compression method not supported',
            \ZIPARCHIVE::ER_EOF         => 'Premature EOF',
            \ZIPARCHIVE::ER_INVAL       => 'Invalid argument',
            \ZIPARCHIVE::ER_NOZIP       => 'Not a zip archive',
            \ZIPARCHIVE::ER_INTERNAL    => 'Internal error',
            \ZIPARCHIVE::ER_INCONS      => 'Zip archive inconsistent',
            \ZIPARCHIVE::ER_REMOVE      => 'Can\'t remove file',
            \ZIPARCHIVE::ER_DELETED     => 'Entry has been deleted',
        );

        if (isset($statusStrings[$this->ziparchive->status])) {
            $statusString = $statusStrings[$this->ziparchive->status];
        } else {
            $statusString = 'Unknown status';
        }
        return $statusString . '(' . $this->ziparchive->status . ')';
    }
}

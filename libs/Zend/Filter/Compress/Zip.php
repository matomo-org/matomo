<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Zip.php 20126 2010-01-07 18:10:58Z ralph $
 */

/**
 * @see Zend_Filter_Compress_CompressAbstract
 */
// require_once 'Zend/Filter/Compress/CompressAbstract.php';

/**
 * Compression adapter for zip
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Compress_Zip extends Zend_Filter_Compress_CompressAbstract
{
    /**
     * Compression Options
     * array(
     *     'archive'  => Archive to use
     *     'password' => Password to use
     *     'target'   => Target to write the files to
     * )
     *
     * @var array
     */
    protected $_options = array(
        'archive' => null,
        'target'  => null,
    );

    /**
     * Class constructor
     *
     * @param string|array $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        if (!extension_loaded('zip')) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This filter needs the zip extension');
        }
        parent::__construct($options);
    }

    /**
     * Returns the set archive
     *
     * @return string
     */
    public function getArchive()
    {
        return $this->_options['archive'];
    }

    /**
     * Sets the archive to use for de-/compression
     *
     * @param string $archive Archive to use
     * @return Zend_Filter_Compress_Rar
     */
    public function setArchive($archive)
    {
        $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $archive);
        $this->_options['archive'] = (string) $archive;

        return $this;
    }

    /**
     * Returns the set targetpath
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->_options['target'];
    }

    /**
     * Sets the target to use
     *
     * @param string $target
     * @return Zend_Filter_Compress_Rar
     */
    public function setTarget($target)
    {
        if (!file_exists(dirname($target))) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("The directory '$target' does not exist");
        }

        $target = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $target);
        $this->_options['target'] = (string) $target;
        return $this;
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string Compressed archive
     */
    public function compress($content)
    {
        $zip = new ZipArchive();
        $res = $zip->open($this->getArchive(), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($res !== true) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception($this->_errorString($res));
        }

        if (file_exists($content)) {
            $content  = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));
            $basename = substr($content, strrpos($content, DIRECTORY_SEPARATOR) + 1);
            if (is_dir($content)) {
                $index    = strrpos($content, DIRECTORY_SEPARATOR) + 1;
                $content .= DIRECTORY_SEPARATOR;
                $stack    = array($content);
                while (!empty($stack)) {
                    $current = array_pop($stack);
                    $files   = array();

                    $dir = dir($current);
                    while (false !== ($node = $dir->read())) {
                        if (($node == '.') || ($node == '..')) {
                            continue;
                        }

                        if (is_dir($current . $node)) {
                            array_push($stack, $current . $node . DIRECTORY_SEPARATOR);
                        }

                        if (is_file($current . $node)) {
                            $files[] = $node;
                        }
                    }

                    $local = substr($current, $index);
                    $zip->addEmptyDir(substr($local, 0, -1));

                    foreach ($files as $file) {
                        $zip->addFile($current . $file, $local . $file);
                        if ($res !== true) {
                            // require_once 'Zend/Filter/Exception.php';
                            throw new Zend_Filter_Exception($this->_errorString($res));
                        }
                    }
                }
            } else {
                $res = $zip->addFile($content, $basename);
                if ($res !== true) {
                    // require_once 'Zend/Filter/Exception.php';
                    throw new Zend_Filter_Exception($this->_errorString($res));
                }
            }
        } else {
            $file = $this->getTarget();
            if (!is_dir($file)) {
                $file = basename($file);
            } else {
                $file = "zip.tmp";
            }

            $res = $zip->addFromString($file, $content);
            if ($res !== true) {
                // require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception($this->_errorString($res));
            }
        }

        $zip->close();
        return $this->_options['archive'];
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return string
     */
    public function decompress($content)
    {
        $archive = $this->getArchive();
        if (file_exists($content)) {
            $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));
        } elseif (empty($archive) || !file_exists($archive)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('ZIP Archive not found');
        }

        $zip = new ZipArchive();
        $res = $zip->open($archive);

        $target = $this->getTarget();

        if (!empty($target) && !is_dir($target)) {
            $target = dirname($target);
        }
        
        if (!empty($target)) {
            $target = rtrim($target, '/\\') . DIRECTORY_SEPARATOR;
        }

        if (empty($target) || !is_dir($target)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('No target for ZIP decompression set');
        }

        if ($res !== true) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception($this->_errorString($res));
        }

        if (version_compare(PHP_VERSION, '5.2.8', '<')) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $statIndex = $zip->statIndex($i);
                $currName = $statIndex['name'];
                if (($currName{0} == '/') ||
                    (substr($currName, 0, 2) == '..') ||
                    (substr($currName, 0, 4) == './..')
                    )
                {
                    // require_once 'Zend/Filter/Exception.php';
                    throw new Zend_Filter_Exception('Upward directory traversal was detected inside ' . $archive
                        . ' please use PHP 5.2.8 or greater to take advantage of path resolution features of '
                        . 'the zip extension in this decompress() method.'
                        );
                }
            }
        }    
        
        $res = @$zip->extractTo($target);
        if ($res !== true) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception($this->_errorString($res));
        }

        $zip->close();
        return $target;
    }

    /**
     * Returns the proper string based on the given error constant
     *
     * @param string $error
     */
    protected function _errorString($error)
    {
        switch($error) {
            case ZipArchive::ER_MULTIDISK :
                return 'Multidisk ZIP Archives not supported';

            case ZipArchive::ER_RENAME :
                return 'Failed to rename the temporary file for ZIP';

            case ZipArchive::ER_CLOSE :
                return 'Failed to close the ZIP Archive';

            case ZipArchive::ER_SEEK :
                return 'Failure while seeking the ZIP Archive';

            case ZipArchive::ER_READ :
                return 'Failure while reading the ZIP Archive';

            case ZipArchive::ER_WRITE :
                return 'Failure while writing the ZIP Archive';

            case ZipArchive::ER_CRC :
                return 'CRC failure within the ZIP Archive';

            case ZipArchive::ER_ZIPCLOSED :
                return 'ZIP Archive already closed';

            case ZipArchive::ER_NOENT :
                return 'No such file within the ZIP Archive';

            case ZipArchive::ER_EXISTS :
                return 'ZIP Archive already exists';

            case ZipArchive::ER_OPEN :
                return 'Can not open ZIP Archive';

            case ZipArchive::ER_TMPOPEN :
                return 'Failure creating temporary ZIP Archive';

            case ZipArchive::ER_ZLIB :
                return 'ZLib Problem';

            case ZipArchive::ER_MEMORY :
                return 'Memory allocation problem while working on a ZIP Archive';

            case ZipArchive::ER_CHANGED :
                return 'ZIP Entry has been changed';

            case ZipArchive::ER_COMPNOTSUPP :
                return 'Compression method not supported within ZLib';

            case ZipArchive::ER_EOF :
                return 'Premature EOF within ZIP Archive';

            case ZipArchive::ER_INVAL :
                return 'Invalid argument for ZLIB';

            case ZipArchive::ER_NOZIP :
                return 'Given file is no zip archive';

            case ZipArchive::ER_INTERNAL :
                return 'Internal error while working on a ZIP Archive';

            case ZipArchive::ER_INCONS :
                return 'Inconsistent ZIP archive';

            case ZipArchive::ER_REMOVE :
                return 'Can not remove ZIP Archive';

            case ZipArchive::ER_DELETED :
                return 'ZIP Entry has been deleted';

            default :
                return 'Unknown error within ZIP Archive';
        }
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Zip';
    }
}

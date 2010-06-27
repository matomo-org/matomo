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
 * @version    $Id: Tar.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_Compress_CompressAbstract
 */
// require_once 'Zend/Filter/Compress/CompressAbstract.php';

/**
 * Compression adapter for Tar
 *
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_Compress_Tar extends Zend_Filter_Compress_CompressAbstract
{
    /**
     * Compression Options
     * array(
     *     'archive'  => Archive to use
     *     'target'   => Target to write the files to
     * )
     *
     * @var array
     */
    protected $_options = array(
        'archive'  => null,
        'target'   => '.',
        'mode'     => null,
    );

    /**
     * Class constructor
     *
     * @param array $options (Optional) Options to set
     */
    public function __construct($options = null)
    {
        // if (!class_exists('Archive_Tar')) {
            // require_once 'Zend/Loader.php';
            // try {
                // Zend_Loader::loadClass('Archive_Tar');
            // } catch (Zend_Exception $e) {
                // require_once 'Zend/Filter/Exception.php';
                // throw new Zend_Filter_Exception('This filter needs PEARs Archive_Tar', 0, $e);
            // }
        // }

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
     * @return Zend_Filter_Compress_Tar
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
     * Sets the targetpath to use
     *
     * @param string $target
     * @return Zend_Filter_Compress_Tar
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
     * Returns the set compression mode
     */
    public function getMode()
    {
        return $this->_options['mode'];
    }

    /**
     * Compression mode to use
     * Eighter Gz or Bz2
     *
     * @param string $mode
     */
    public function setMode($mode)
    {
        $mode = ucfirst(strtolower($mode));
        if (($mode != 'Bz2') && ($mode != 'Gz')) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception("The mode '$mode' is unknown");
        }

        if (($mode == 'Bz2') && (!extension_loaded('bz2'))) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This mode needs the bz2 extension');
        }

        if (($mode == 'Gz') && (!extension_loaded('zlib'))) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('This mode needs the zlib extension');
        }
    }

    /**
     * Compresses the given content
     *
     * @param  string $content
     * @return string
     */
    public function compress($content)
    {
        $archive = new Archive_Tar($this->getArchive(), $this->getMode());
        if (!file_exists($content)) {
            $file = $this->getTarget();
            if (is_dir($file)) {
                $file .= DIRECTORY_SEPARATOR . "tar.tmp";
            }

            $result = file_put_contents($file, $content);
            if ($result === false) {
                // require_once 'Zend/Filter/Exception.php';
                throw new Zend_Filter_Exception('Error creating the temporary file');
            }

            $content = $file;
        }

        if (is_dir($content)) {
            // collect all file infos
            foreach (new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator($content, RecursiveDirectoryIterator::KEY_AS_PATHNAME),
                        RecursiveIteratorIterator::SELF_FIRST
                    ) as $directory => $info
            ) {
                if ($info->isFile()) {
                    $file[] = $directory;
                }
            }

            $content = $file;
        }

        $result  = $archive->create($content);
        if ($result === false) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Error creating the Tar archive');
        }

        return $this->getArchive();
    }

    /**
     * Decompresses the given content
     *
     * @param  string $content
     * @return boolean
     */
    public function decompress($content)
    {
        $archive = $this->getArchive();
        if (file_exists($content)) {
            $archive = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, realpath($content));
        } elseif (empty($archive) || !file_exists($archive)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Tar Archive not found');
        }

        $archive = new Archive_Tar($archive, $this->getMode());
        $target  = $this->getTarget();
        if (!is_dir($target)) {
            $target = dirname($target);
        }

        $result = $archive->extract($target);
        if ($result === false) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Error while extracting the Tar archive');
        }

        return true;
    }

    /**
     * Returns the adapter name
     *
     * @return string
     */
    public function toString()
    {
        return 'Tar';
    }
}

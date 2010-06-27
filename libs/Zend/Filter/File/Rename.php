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
 * @version    $Id: Rename.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Filter_Interface
 */
// require_once 'Zend/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Filter
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Filter_File_Rename implements Zend_Filter_Interface
{
    /**
     * Internal array of array(source, target, overwrite)
     */
    protected $_files = array();

    /**
     * Class constructor
     *
     * Options argument may be either a string, a Zend_Config object, or an array.
     * If an array or Zend_Config object, it accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the sourcefile
     * 'overwrite' => Shall existing files be overwritten ?
     *
     * @param  string|array $options Target file or directory to be renamed
     * @param  string $target Source filename or directory (deprecated)
     * @param  bool $overwrite Should existing files be overwritten (deprecated)
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_string($options)) {
            $options = array('target' => $options);
        } elseif (!is_array($options)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('Invalid options argument provided to filter');
        }

        if (1 < func_num_args()) {
            $argv = func_get_args();
            array_shift($argv);
            $source    = array_shift($argv);
            $overwrite = false;
            if (!empty($argv)) {
                $overwrite = array_shift($argv);
            }
            $options['source']    = $source;
            $options['overwrite'] = $overwrite;
        }

        $this->setFile($options);
    }

    /**
     * Returns the files to rename and their new name and location
     *
     * @return array
     */
    public function getFile()
    {
        return $this->_files;
    }

    /**
     * Sets a new file or directory as target, deleting existing ones
     *
     * Array accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the sourcefile
     * 'overwrite' => Shall existing files be overwritten ?
     *
     * @param  string|array $options Old file or directory to be rewritten
     * @return Zend_Filter_File_Rename
     */
    public function setFile($options)
    {
        $this->_files = array();
        $this->addFile($options);

        return $this;
    }

    /**
     * Adds a new file or directory as target to the existing ones
     *
     * Array accepts the following keys:
     * 'source'    => Source filename or directory which will be renamed
     * 'target'    => Target filename or directory, the new name of the sourcefile
     * 'overwrite' => Shall existing files be overwritten ?
     *
     * @param  string|array $options Old file or directory to be rewritten
     * @return Zend_Filter_File_Rename
     */
    public function addFile($options)
    {
        if (is_string($options)) {
            $options = array('target' => $options);
        } elseif (!is_array($options)) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception ('Invalid options to rename filter provided');
        }

        $this->_convertOptions($options);

        return $this;
    }

    /**
     * Returns only the new filename without moving it
     * But existing files will be erased when the overwrite option is true
     *
     * @param  string  $value  Full path of file to change
     * @param  boolean $source Return internal informations
     * @return string The new filename which has been set
     */
    public function getNewName($value, $source = false)
    {
        $file = $this->_getFileName($value);
        if ($file['source'] == $file['target']) {
            return $value;
        }

        if (!file_exists($file['source'])) {
            return $value;
        }

        if (($file['overwrite'] == true) && (file_exists($file['target']))) {
            unlink($file['target']);
        }

        if (file_exists($file['target'])) {
            // require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception(sprintf("File '%s' could not be renamed. It already exists.", $value));
        }

        if ($source) {
            return $file;
        }

        return $file['target'];
    }

    /**
     * Defined by Zend_Filter_Interface
     *
     * Renames the file $value to the new name set before
     * Returns the file $value, removing all but digit characters
     *
     * @param  string $value Full path of file to change
     * @throws Zend_Filter_Exception
     * @return string The new filename which has been set, or false when there were errors
     */
    public function filter($value)
    {
        $file   = $this->getNewName($value, true);
        if (is_string($file)) {
            return $file;
        }

        $result = rename($file['source'], $file['target']);

        if ($result === true) {
            return $file['target'];
        }

        // require_once 'Zend/Filter/Exception.php';
        throw new Zend_Filter_Exception(sprintf("File '%s' could not be renamed. An error occured while processing the file.", $value));
    }

    /**
     * Internal method for creating the file array
     * Supports single and nested arrays
     *
     * @param  array $options
     * @return array
     */
    protected function _convertOptions($options) {
        $files = array();
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                $this->_convertOptions($value);
                continue;
            }

            switch ($key) {
                case "source":
                    $files['source'] = (string) $value;
                    break;

                case 'target' :
                    $files['target'] = (string) $value;
                    break;

                case 'overwrite' :
                    $files['overwrite'] = (boolean) $value;
                    break;

                default:
                    break;
            }
        }

        if (empty($files)) {
            return $this;
        }

        if (empty($files['source'])) {
            $files['source'] = '*';
        }

        if (empty($files['target'])) {
            $files['target'] = '*';
        }

        if (empty($files['overwrite'])) {
            $files['overwrite'] = false;
        }

        $found = false;
        foreach ($this->_files as $key => $value) {
            if ($value['source'] == $files['source']) {
                $this->_files[$key] = $files;
                $found              = true;
            }
        }

        if (!$found) {
            $count                = count($this->_files);
            $this->_files[$count] = $files;
        }

        return $this;
    }

    /**
     * Internal method to resolve the requested source
     * and return all other related parameters
     *
     * @param  string $file Filename to get the informations for
     * @return array
     */
    protected function _getFileName($file)
    {
        $rename = array();
        foreach ($this->_files as $value) {
            if ($value['source'] == '*') {
                if (!isset($rename['source'])) {
                    $rename           = $value;
                    $rename['source'] = $file;
                }
            }

            if ($value['source'] == $file) {
                $rename = $value;
            }
        }

        if (!isset($rename['source'])) {
            return $file;
        }

        if (!isset($rename['target']) or ($rename['target'] == '*')) {
            $rename['target'] = $rename['source'];
        }

        if (is_dir($rename['target'])) {
            $name = basename($rename['source']);
            $last = $rename['target'][strlen($rename['target']) - 1];
            if (($last != '/') and ($last != '\\')) {
                $rename['target'] .= DIRECTORY_SEPARATOR;
            }

            $rename['target'] .= $name;
        }

        return $rename;
    }
}

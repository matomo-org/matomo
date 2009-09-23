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
 * @category  Zend
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Exists.php 18148 2009-09-16 19:27:43Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validator which checks if the file already exists in the directory
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Exists extends Zend_Validate_Abstract
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_EXIST = 'fileExistsDoesNotExist';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::DOES_NOT_EXIST => "The file '%value%' does not exist"
    );

    /**
     * Internal list of directories
     * @var string
     */
    protected $_directory = '';

    /**
     * @var array Error message template variables
     */
    protected $_messageVariables = array(
        'directory' => '_directory'
    );

    /**
     * Sets validator options
     *
     * @param  string|array|Zend_Config $directory
     * @return void
     */
    public function __construct($directory = array())
    {
        if ($directory instanceof Zend_Config) {
            $directory = $directory->toArray();
        } else if (is_string($directory)) {
            $directory = explode(',', $directory);
        } else if (!is_array($directory)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $this->setDirectory($directory);
    }

    /**
     * Returns the set file directories which are checked
     *
     * @param  boolean $asArray Returns the values as array, when false an concated string is returned
     * @return string
     */
    public function getDirectory($asArray = false)
    {
        $asArray   = (bool) $asArray;
        $directory = (string) $this->_directory;
        if ($asArray) {
            $directory = explode(',', $directory);
        }

        return $directory;
    }

    /**
     * Sets the file directory which will be checked
     *
     * @param  string|array $directory The directories to validate
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function setDirectory($directory)
    {
        $this->_directory = null;
        $this->addDirectory($directory);
        return $this;
    }

    /**
     * Adds the file directory which will be checked
     *
     * @param  string|array $directory The directory to add for validation
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function addDirectory($directory)
    {
        $directories = $this->getDirectory(true);

        if (is_string($directory)) {
            $directory = explode(',', $directory);
        } else if (!is_array($directory)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        foreach ($directory as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }

            $directories[] = trim($content);
        }
        $directories = array_unique($directories);

        // Sanity check to ensure no empty values
        foreach ($directories as $key => $dir) {
            if (empty($dir)) {
                unset($directories[$key]);
            }
        }

        $this->_directory = implode(',', $directories);

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the file already exists in the set directories
     *
     * @param  string  $value Real file to check for existance
     * @param  array   $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        $directories = $this->getDirectory(true);
        if (($file !== null) and (!empty($file['destination']))) {
            $directories[] = $file['destination'];
        } else if (!isset($file['name'])) {
            $file['name'] = $value;
        }

        $check = false;
        foreach ($directories as $directory) {
            if (empty($directory)) {
                continue;
            }

            $check = true;
            if (!file_exists($directory . DIRECTORY_SEPARATOR . $file['name'])) {
                return $this->_throw($file, self::DOES_NOT_EXIST);
            }
        }

        if (!$check) {
            return $this->_throw($file, self::DOES_NOT_EXIST);
        }

        return true;
    }

    /**
     * Throws an error of the given type
     *
     * @param  string $file
     * @param  string $errorType
     * @return false
     */
    protected function _throw($file, $errorType)
    {
        if ($file !== null) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}

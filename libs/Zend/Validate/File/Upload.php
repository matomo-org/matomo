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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Upload.php 22399 2010-06-09 19:08:28Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the maximum size of a file up to a max of 2GB
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Upload extends Zend_Validate_Abstract
{
    /**@#+
     * @const string Error constants
     */
    const INI_SIZE       = 'fileUploadErrorIniSize';
    const FORM_SIZE      = 'fileUploadErrorFormSize';
    const PARTIAL        = 'fileUploadErrorPartial';
    const NO_FILE        = 'fileUploadErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadErrorCantWrite';
    const EXTENSION      = 'fileUploadErrorExtension';
    const ATTACK         = 'fileUploadErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadErrorFileNotFound';
    const UNKNOWN        = 'fileUploadErrorUnknown';
    /**@#-*/

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::INI_SIZE       => "File '%value%' exceeds the defined ini size",
        self::FORM_SIZE      => "File '%value%' exceeds the defined form size",
        self::PARTIAL        => "File '%value%' was only partially uploaded",
        self::NO_FILE        => "File '%value%' was not uploaded",
        self::NO_TMP_DIR     => "No temporary directory was found for file '%value%'",
        self::CANT_WRITE     => "File '%value%' can't be written",
        self::EXTENSION      => "A PHP extension returned an error while uploading the file '%value%'",
        self::ATTACK         => "File '%value%' was illegally uploaded. This could be a possible attack",
        self::FILE_NOT_FOUND => "File '%value%' was not found",
        self::UNKNOWN        => "Unknown error while uploading file '%value%'"
    );

    /**
     * Internal array of files
     * @var array
     */
    protected $_files = array();

    /**
     * Sets validator options
     *
     * The array $files must be given in syntax of Zend_File_Transfer to be checked
     * If no files are given the $_FILES array will be used automatically.
     * NOTE: This validator will only work with HTTP POST uploads!
     *
     * @param  array|Zend_Config $files Array of files in syntax of Zend_File_Transfer
     * @return void
     */
    public function __construct($files = array())
    {
        if ($files instanceof Zend_Config) {
            $files = $files->toArray();
        }

        $this->setFiles($files);
    }

    /**
     * Returns the array of set files
     *
     * @param  string $files (Optional) The file to return in detail
     * @return array
     * @throws Zend_Validate_Exception If file is not found
     */
    public function getFiles($file = null)
    {
        if ($file !== null) {
            $return = array();
            foreach ($this->_files as $name => $content) {
                if ($name === $file) {
                    $return[$file] = $this->_files[$name];
                }

                if ($content['name'] === $file) {
                    $return[$name] = $this->_files[$name];
                }
            }

            if (count($return) === 0) {
                // require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception("The file '$file' was not found");
            }

            return $return;
        }

        return $this->_files;
    }

    /**
     * Sets the files to be checked
     *
     * @param  array $files The files to check in syntax of Zend_File_Transfer
     * @return Zend_Validate_File_Upload Provides a fluent interface
     */
    public function setFiles($files = array())
    {
        if (count($files) === 0) {
            $this->_files = $_FILES;
        } else {
            $this->_files = $files;
        }

        foreach($this->_files as $file => $content) {
            if (!isset($content['error'])) {
                unset($this->_files[$file]);
            }
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the file was uploaded without errors
     *
     * @param  string $value Single file to check for upload errors, when giving null the $_FILES array
     *                       from initialization will be used
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        $this->_messages = null;
        if (array_key_exists($value, $this->_files)) {
            $files[$value] = $this->_files[$value];
        } else {
            foreach ($this->_files as $file => $content) {
                if (isset($content['name']) && ($content['name'] === $value)) {
                    $files[$file] = $this->_files[$file];
                }

                if (isset($content['tmp_name']) && ($content['tmp_name'] === $value)) {
                    $files[$file] = $this->_files[$file];
                }
            }
        }

        if (empty($files)) {
            return $this->_throw($file, self::FILE_NOT_FOUND);
        }

        foreach ($files as $file => $content) {
            $this->_value = $file;
            switch($content['error']) {
                case 0:
                    if (!is_uploaded_file($content['tmp_name'])) {
                        $this->_throw($file, self::ATTACK);
                    }
                    break;

                case 1:
                    $this->_throw($file, self::INI_SIZE);
                    break;

                case 2:
                    $this->_throw($file, self::FORM_SIZE);
                    break;

                case 3:
                    $this->_throw($file, self::PARTIAL);
                    break;

                case 4:
                    $this->_throw($file, self::NO_FILE);
                    break;

                case 6:
                    $this->_throw($file, self::NO_TMP_DIR);
                    break;

                case 7:
                    $this->_throw($file, self::CANT_WRITE);
                    break;

                case 8:
                    $this->_throw($file, self::EXTENSION);
                    break;

                default:
                    $this->_throw($file, self::UNKNOWN);
                    break;
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        } else {
            return true;
        }
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
            if (is_array($file) and !empty($file['name'])) {
                $this->_value = $file['name'];
            }
        }

        $this->_error($errorType);
        return false;
    }
}

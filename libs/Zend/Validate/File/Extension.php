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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Extension.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the file extension of a file
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Extension extends Zend_Validate_Abstract
{
    /**
     * @const string Error constants
     */
    const FALSE_EXTENSION = 'fileExtensionFalse';
    const NOT_FOUND       = 'fileExtensionNotFound';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::FALSE_EXTENSION => "File '%value%' has a false extension",
        self::NOT_FOUND       => "File '%value%' is not readable or does not exist",
    );

    /**
     * Internal list of extensions
     * @var string
     */
    protected $_extension = '';

    /**
     * Validate case sensitive
     *
     * @var boolean
     */
    protected $_case = false;

    /**
     * @var array Error message template variables
     */
    protected $_messageVariables = array(
        'extension' => '_extension'
    );

    /**
     * Sets validator options
     *
     * @param  string|array|Zend_Config $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (1 < func_num_args()) {
            $case = func_get_arg(1);
            $this->setCase($case);
        }

        if (is_array($options) and isset($options['case'])) {
            $this->setCase($options['case']);
            unset($options['case']);
        }

        $this->setExtension($options);
    }

    /**
     * Returns the case option
     *
     * @return boolean
     */
    public function getCase()
    {
        return $this->_case;
    }

    /**
     * Sets the case to use
     *
     * @param  boolean $case
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function setCase($case)
    {
        $this->_case = (boolean) $case;
        return $this;
    }

    /**
     * Returns the set file extension
     *
     * @return array
     */
    public function getExtension()
    {
        $extension = explode(',', $this->_extension);

        return $extension;
    }

    /**
     * Sets the file extensions
     *
     * @param  string|array $extension The extensions to validate
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function setExtension($extension)
    {
        $this->_extension = null;
        $this->addExtension($extension);
        return $this;
    }

    /**
     * Adds the file extensions
     *
     * @param  string|array $extension The extensions to add for validation
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function addExtension($extension)
    {
        $extensions = $this->getExtension();
        if (is_string($extension)) {
            $extension = explode(',', $extension);
        }

        foreach ($extension as $content) {
            if (empty($content) || !is_string($content)) {
                continue;
            }

            $extensions[] = trim($content);
        }
        $extensions = array_unique($extensions);

        // Sanity check to ensure no empty values
        foreach ($extensions as $key => $ext) {
            if (empty($ext)) {
                unset($extensions[$key]);
            }
        }

        $this->_extension = implode(',', $extensions);

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the fileextension of $value is included in the
     * set extension list
     *
     * @param  string  $value Real file to check for extension
     * @param  array   $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        // require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        if ($file !== null) {
            $info['extension'] = substr($file['name'], strrpos($file['name'], '.') + 1);
        } else {
            $info = pathinfo($value);
        }

        $extensions = $this->getExtension();

        if ($this->_case && (in_array($info['extension'], $extensions))) {
            return true;
        } else if (!$this->getCase()) {
            foreach ($extensions as $extension) {
                if (strtolower($extension) == strtolower($info['extension'])) {
                    return true;
                }
            }
        }

        return $this->_throw($file, self::FALSE_EXTENSION);
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
        if (null !== $file) {
            $this->_value = $file['name'];
        }

        $this->_error($errorType);
        return false;
    }
}

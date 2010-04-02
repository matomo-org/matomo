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
 * @version   $Id: Count.php 21326 2010-03-04 20:32:39Z thomas $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for counting all given files
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Count extends Zend_Validate_Abstract
{
    /**#@+
     * @const string Error constants
     */
    const TOO_MANY = 'fileCountTooMany';
    const TOO_FEW  = 'fileCountTooFew';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::TOO_MANY => "Too many files, maximum '%max%' are allowed but '%count%' are given",
        self::TOO_FEW  => "Too few files, minimum '%min%' are expected but '%count%' are given",
    );

    /**
     * @var array Error message template variables
     */
    protected $_messageVariables = array(
        'min'   => '_min',
        'max'   => '_max',
        'count' => '_count'
    );

    /**
     * Minimum file count
     *
     * If null, there is no minimum file count
     *
     * @var integer
     */
    protected $_min;

    /**
     * Maximum file count
     *
     * If null, there is no maximum file count
     *
     * @var integer|null
     */
    protected $_max;

    /**
     * Actual filecount
     *
     * @var integer
     */
    protected $_count;

    /**
     * Internal file array
     * @var array
     */
    protected $_files;

    /**
     * Sets validator options
     *
     * Min limits the file count, when used with max=null it is the maximum file count
     * It also accepts an array with the keys 'min' and 'max'
     *
     * If $options is a integer, it will be used as maximum file count
     * As Array is accepts the following keys:
     * 'min': Minimum filecount
     * 'max': Maximum filecount
     *
     * @param  integer|array|Zend_Config $options Options for the adapter
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_string($options) || is_numeric($options)) {
            $options = array('max' => $options);
        } elseif (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        if (1 < func_num_args()) {
            $options['min'] = func_get_arg(0);
            $options['max'] = func_get_arg(1);
        }

        if (isset($options['min'])) {
            $this->setMin($options);
        }

        if (isset($options['max'])) {
            $this->setMax($options);
        }
    }

    /**
     * Returns the minimum file count
     *
     * @return integer
     */
    public function getMin()
    {
        return $this->_min;
    }

    /**
     * Sets the minimum file count
     *
     * @param  integer|array $min The minimum file count
     * @return Zend_Validate_File_Count Provides a fluent interface
     * @throws Zend_Validate_Exception When min is greater than max
     */
    public function setMin($min)
    {
        if (is_array($min) and isset($min['min'])) {
            $min = $min['min'];
        }

        if (!is_string($min) and !is_numeric($min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $min = (integer) $min;
        if (($this->_max !== null) && ($min > $this->_max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum file count, but $min >"
                                            . " {$this->_max}");
        }

        $this->_min = $min;
        return $this;
    }

    /**
     * Returns the maximum file count
     *
     * @return integer
     */
    public function getMax()
    {
        return $this->_max;
    }

    /**
     * Sets the maximum file count
     *
     * @param  integer|array $max The maximum file count
     * @return Zend_Validate_StringLength Provides a fluent interface
     * @throws Zend_Validate_Exception When max is smaller than min
     */
    public function setMax($max)
    {
        if (is_array($max) and isset($max['max'])) {
            $max = $max['max'];
        }

        if (!is_string($max) and !is_numeric($max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $max = (integer) $max;
        if (($this->_min !== null) && ($max < $this->_min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum file count, but "
                                            . "$max < {$this->_min}");
        }

        $this->_max = $max;
        return $this;
    }

    /**
     * Adds a file for validation
     *
     * @param string|array $file
     */
    public function addFile($file)
    {
        if (is_string($file)) {
            $file = array($file);
        }

        if (is_array($file)) {
            foreach ($file as $name) {
                if (!isset($this->_files[$name]) && !empty($name)) {
                    $this->_files[$name] = $name;
                }
            }
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the file count of all checked files is at least min and
     * not bigger than max (when max is not null). Attention: When checking with set min you
     * must give all files with the first call, otherwise you will get an false.
     *
     * @param  string|array $value Filenames to check for count
     * @param  array        $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if (($file !== null) && !array_key_exists('destination', $file)) {
            $file['destination'] = dirname($value);
        }

        if (($file !== null) && array_key_exists('tmp_name', $file)) {
            $value = $file['destination'] . DIRECTORY_SEPARATOR . $file['name'];
        }

        if (($file === null) || !empty($file['tmp_name'])) {
            $this->addFile($value);
        }

        $this->_count = count($this->_files);
        if (($this->_max !== null) && ($this->_count > $this->_max)) {
            return $this->_throw($file, self::TOO_MANY);
        }

        if (($this->_min !== null) && ($this->_count < $this->_min)) {
            return $this->_throw($file, self::TOO_FEW);
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

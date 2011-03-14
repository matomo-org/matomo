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
 * @version   $Id: Size.php 23775 2011-03-01 17:25:24Z ralph $
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
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Size extends Zend_Validate_Abstract
{
    /**#@+
     * @const string Error constants
     */
    const TOO_BIG   = 'fileSizeTooBig';
    const TOO_SMALL = 'fileSizeTooSmall';
    const NOT_FOUND = 'fileSizeNotFound';
    /**#@-*/

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::TOO_BIG   => "Maximum allowed size for file '%value%' is '%max%' but '%size%' detected",
        self::TOO_SMALL => "Minimum expected size for file '%value%' is '%min%' but '%size%' detected",
        self::NOT_FOUND => "File '%value%' is not readable or does not exist",
    );

    /**
     * @var array Error message template variables
     */
    protected $_messageVariables = array(
        'min'  => '_min',
        'max'  => '_max',
        'size' => '_size',
    );

    /**
     * Minimum filesize
     * @var integer
     */
    protected $_min;

    /**
     * Maximum filesize
     *
     * If null, there is no maximum filesize
     *
     * @var integer|null
     */
    protected $_max;

    /**
     * Detected size
     *
     * @var integer
     */
    protected $_size;

    /**
     * Use bytestring ?
     *
     * @var boolean
     */
    protected $_useByteString = true;

    /**
     * Sets validator options
     *
     * If $options is a integer, it will be used as maximum filesize
     * As Array is accepts the following keys:
     * 'min': Minimum filesize
     * 'max': Maximum filesize
     * 'bytestring': Use bytestring or real size for messages
     *
     * @param  integer|array $options Options for the adapter
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_string($options) || is_numeric($options)) {
            $options = array('max' => $options);
        } elseif (!is_array($options)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        if (1 < func_num_args()) {
            $argv = func_get_args();
            array_shift($argv);
            $options['max'] = array_shift($argv);
            if (!empty($argv)) {
                $options['bytestring'] = array_shift($argv);
            }
        }

        if (isset($options['bytestring'])) {
            $this->setUseByteString($options['bytestring']);
        }

        if (isset($options['min'])) {
            $this->setMin($options['min']);
        }

        if (isset($options['max'])) {
            $this->setMax($options['max']);
        }
    }

    /**
     * Returns the minimum filesize
     *
     * @param  boolean $byteString Use bytestring ?
     * @return integer
     */
    public function setUseByteString($byteString = true)
    {
        $this->_useByteString = (bool) $byteString;
        return $this;
    }

    /**
     * Will bytestring be used?
     *
     * @return boolean
     */
    public function useByteString()
    {
        return $this->_useByteString;
    }

    /**
     * Returns the minimum filesize
     *
     * @param  bool $raw Whether or not to force return of the raw value (defaults off)
     * @return integer|string
     */
    public function getMin($raw = false)
    {
        $min = $this->_min;
        if (!$raw && $this->useByteString()) {
            $min = $this->_toByteString($min);
        }

        return $min;
    }

    /**
     * Sets the minimum filesize
     *
     * @param  integer $min The minimum filesize
     * @throws Zend_Validate_Exception When min is greater than max
     * @return Zend_Validate_File_Size Provides a fluent interface
     */
    public function setMin($min)
    {
        if (!is_string($min) and !is_numeric($min)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $min = (integer) $this->_fromByteString($min);
        $max = $this->getMax(true);
        if (($max !== null) && ($min > $max)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum filesize, but $min >"
                                            . " $max");
        }

        $this->_min = $min;
        return $this;
    }

    /**
     * Returns the maximum filesize
     *
     * @param  bool $raw Whether or not to force return of the raw value (defaults off)
     * @return integer|string
     */
    public function getMax($raw = false)
    {
        $max = $this->_max;
        if (!$raw && $this->useByteString()) {
            $max = $this->_toByteString($max);
        }

        return $max;
    }

    /**
     * Sets the maximum filesize
     *
     * @param  integer $max The maximum filesize
     * @throws Zend_Validate_Exception When max is smaller than min
     * @return Zend_Validate_StringLength Provides a fluent interface
     */
    public function setMax($max)
    {
        if (!is_string($max) && !is_numeric($max)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception ('Invalid options to validator provided');
        }

        $max = (integer) $this->_fromByteString($max);
        $min = $this->getMin(true);
        if (($min !== null) && ($max < $min)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum filesize, but "
                                            . "$max < $min");
        }

        $this->_max = $max;
        return $this;
    }

    /**
     * Retrieve current detected file size
     *
     * @return int
     */
    protected function _getSize()
    {
        return $this->_size;
    }

    /**
     * Set current size
     *
     * @param  int $size
     * @return Zend_Validate_File_Size
     */
    protected function _setSize($size)
    {
        $this->_size = $size;
        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the filesize of $value is at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string $value Real file to check for size
     * @param  array  $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        // require_once 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_FOUND);
        }

        // limited to 4GB files
        $size        = sprintf("%u", @filesize($value));
        $this->_size = $size;

        // Check to see if it's smaller than min size
        $min = $this->getMin(true);
        $max = $this->getMax(true);
        if (($min !== null) && ($size < $min)) {
            if ($this->useByteString()) {
                $this->_min  = $this->_toByteString($min);
                $this->_size = $this->_toByteString($size);
                $this->_throw($file, self::TOO_SMALL);
                $this->_min  = $min;
                $this->_size = $size;
            } else {
                $this->_throw($file, self::TOO_SMALL);
            }
        }

        // Check to see if it's larger than max size
        if (($max !== null) && ($max < $size)) {
            if ($this->useByteString()) {
                $this->_max  = $this->_toByteString($max);
                $this->_size = $this->_toByteString($size);
                $this->_throw($file, self::TOO_BIG);
                $this->_max  = $max;
                $this->_size = $size;
            } else {
                $this->_throw($file, self::TOO_BIG);
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Returns the formatted size
     *
     * @param  integer $size
     * @return string
     */
    protected function _toByteString($size)
    {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size >= 1024 && $i < 9; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . $sizes[$i];
    }

    /**
     * Returns the unformatted size
     *
     * @param  string $size
     * @return integer
     */
    protected function _fromByteString($size)
    {
        if (is_numeric($size)) {
            return (integer) $size;
        }

        $type  = trim(substr($size, -2, 1));

        $value = substr($size, 0, -1);
        if (!is_numeric($value)) {
            $value = substr($value, 0, -1);
        }

        switch (strtoupper($type)) {
            case 'Y':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'Z':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'E':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'P':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'T':
                $value *= (1024 * 1024 * 1024 * 1024);
                break;
            case 'G':
                $value *= (1024 * 1024 * 1024);
                break;
            case 'M':
                $value *= (1024 * 1024);
                break;
            case 'K':
                $value *= 1024;
                break;
            default:
                break;
        }

        return $value;
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

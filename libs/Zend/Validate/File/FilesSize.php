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
 * @version   $Id: FilesSize.php 18148 2009-09-16 19:27:43Z thomas $
 */

/**
 * @see Zend_Validate_File_Size
 */
require_once 'Zend/Validate/File/Size.php';

/**
 * Validator for the size of all files which will be validated in sum
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_FilesSize extends Zend_Validate_File_Size
{
    /**
     * @const string Error constants
     */
    const TOO_BIG      = 'fileFilesSizeTooBig';
    const TOO_SMALL    = 'fileFilesSizeTooSmall';
    const NOT_READABLE = 'fileFilesSizeNotReadable';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::TOO_BIG      => "All files in sum should have a maximum size of '%max%' but '%size%' were detected",
        self::TOO_SMALL    => "All files in sum should have a minimum size of '%min%' but '%size%' were detected",
        self::NOT_READABLE => "One or more files can not be read"
    );

    /**
     * Internal file array
     *
     * @var array
     */
    protected $_files;

    /**
     * Sets validator options
     *
     * Min limits the used diskspace for all files, when used with max=null it is the maximum filesize
     * It also accepts an array with the keys 'min' and 'max'
     *
     * @param  integer|array|Zend_Config $options Options for this validator
     * @return void
     */
    public function __construct($options)
    {
        $this->_files = array();
        $this->_setSize(0);

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_scalar($options)) {
            $options = array('max' => $options);
        } elseif (!is_array($options)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid options to validator provided');
        }

        if (1 < func_num_args()) {
// @todo: Preperation for 2.0... needs to be cleared with the dev-team
//          trigger_error('Multiple constructor options are deprecated in favor of a single options array', E_USER_NOTICE);

            $argv = func_get_args();
            array_shift($argv);
            $options['max'] = array_shift($argv);
            if (!empty($argv)) {
                $options['bytestring'] = array_shift($argv);
            }
        }

        parent::__construct($options);
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the disk usage of all files is at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string|array $value Real file to check for size
     * @param  array        $file  File data from Zend_File_Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        require_once 'Zend/Loader.php';
        if (is_string($value)) {
            $value = array($value);
        }

        $min  = $this->getMin(true);
        $max  = $this->getMax(true);
        $size = $this->_getSize();
        foreach ($value as $files) {
            // Is file readable ?
            if (!Zend_Loader::isReadable($files)) {
                $this->_throw($file, self::NOT_READABLE);
                continue;
            }

            if (!isset($this->_files[$files])) {
                $this->_files[$files] = $files;
            } else {
                // file already counted... do not count twice
                continue;
            }

            // limited to 2GB files
            $size += @filesize($files);
            $this->_setSize($size);
            if (($max !== null) && ($max < $size)) {
                if ($this->useByteString()) {
                    $this->setMax($this->_toByteString($max));
                    $this->_throw($file, self::TOO_BIG);
                    $this->setMax($max);
                } else {
                    $this->_throw($file, self::TOO_BIG);
                }
            }
        }

        // Check that aggregate files are >= minimum size
        if (($min !== null) && ($size < $min)) {
            if ($this->useByteString()) {
                $this->setMin($this->_toByteString($min));
                $this->_throw($file, self::TOO_SMALL);
                $this->setMin($min);
            } else {
                $this->_throw($file, self::TOO_SMALL);
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        }

        return true;
    }
}

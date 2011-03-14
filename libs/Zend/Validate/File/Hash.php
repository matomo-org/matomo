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
 * @version   $Id: Hash.php 23775 2011-03-01 17:25:24Z ralph $
 */

/**
 * @see Zend_Validate_Abstract
 */
// require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the hash of given files
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Hash extends Zend_Validate_Abstract
{
    /**
     * @const string Error constants
     */
    const DOES_NOT_MATCH = 'fileHashDoesNotMatch';
    const NOT_DETECTED   = 'fileHashHashNotDetected';
    const NOT_FOUND      = 'fileHashNotFound';

    /**
     * @var array Error message templates
     */
    protected $_messageTemplates = array(
        self::DOES_NOT_MATCH => "File '%value%' does not match the given hashes",
        self::NOT_DETECTED   => "A hash could not be evaluated for the given file",
        self::NOT_FOUND      => "File '%value%' is not readable or does not exist"
    );

    /**
     * Hash of the file
     *
     * @var string
     */
    protected $_hash;

    /**
     * Sets validator options
     *
     * @param  string|array $options
     * @return void
     */
    public function __construct($options)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (is_scalar($options)) {
            $options = array('hash1' => $options);
        } elseif (!is_array($options)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('Invalid options to validator provided');
        }

        if (1 < func_num_args()) {
            $options['algorithm'] = func_get_arg(1);
        }

        $this->setHash($options);
    }

    /**
     * Returns the set hash values as array, the hash as key and the algorithm the value
     *
     * @return array
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * Sets the hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function setHash($options)
    {
        $this->_hash  = null;
        $this->addHash($options);

        return $this;
    }

    /**
     * Adds the hash for one or multiple files
     *
     * @param  string|array $options
     * @return Zend_Validate_File_Hash Provides a fluent interface
     */
    public function addHash($options)
    {
        if (is_string($options)) {
            $options = array($options);
        } else if (!is_array($options)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("False parameter given");
        }

        $known = hash_algos();
        if (!isset($options['algorithm'])) {
            $algorithm = 'crc32';
        } else {
            $algorithm = $options['algorithm'];
            unset($options['algorithm']);
        }

        if (!in_array($algorithm, $known)) {
            // require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("Unknown algorithm '{$algorithm}'");
        }

        foreach ($options as $value) {
            $this->_hash[$value] = $algorithm;
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the given file confirms the set hash
     *
     * @param  string $value Filename to check for hash
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

        $algos  = array_unique(array_values($this->_hash));
        $hashes = array_unique(array_keys($this->_hash));
        foreach ($algos as $algorithm) {
            $filehash = hash_file($algorithm, $value);
            if ($filehash === false) {
                return $this->_throw($file, self::NOT_DETECTED);
            }

            foreach($hashes as $hash) {
                if ($filehash === $hash) {
                    return true;
                }
            }
        }

        return $this->_throw($file, self::DOES_NOT_MATCH);
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
